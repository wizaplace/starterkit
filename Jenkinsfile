pipeline {
    options {
        buildDiscarder(logRotator(daysToKeepStr: '', numToKeepStr: '10', artifactDaysToKeepStr: '', artifactNumToKeepStr: ''))
    }

    agent none

    stages {
        stage('composer install') {
            agent {
                docker {
                    image 'composer'
                    args '-v composer-cache:/composer -u 0:0'
                }
            }
            steps {
                withCredentials([string(credentialsId: 'e18082c0-a95c-4c22-9bf5-803fd091c764', variable: 'GITHUB_TOKEN')]) {
                    sh 'rm -rf var/logs/*'
                    sh 'rm -rf var/cache/*'
                    sh 'rm -rf var/screenshots/*'
                    sh 'echo -e "machine github.com\n  login $GITHUB_TOKEN" >> ~/.netrc'
                    sh 'composer config -g github-oauth.github.com $GITHUB_TOKEN'
                    sh 'composer install --no-interaction --no-progress --ignore-platform-reqs'
                }
            }
        }
        stage('gulp install') {
            agent {
                docker {
                    image 'myprod/gulp'
                    args '-v npm-cache:/root/.npm -u 0:0'
                }
            }
            steps {
                sh 'make npm-install lint-css assets'
            }
        }
        stage('lint') {
            agent {
                docker {
                    image 'php:7.2'
                    args '-u 0:0'
                }
            }
            steps {
                parallel(
                    'phpcs': {
                        sh 'make lint-php'
                    },
                    'stan': {
                        sh 'make stan'
                    },
                    'twig': {
                        sh 'make lint-twig'
                    },
                    'yaml': {
                        sh 'make lint-yaml'
                    },
                    'xliff': {
                        sh 'make lint-xliff'
                    },
                    'json': {
                        sh 'make lint-json'
                    }
                )
            }
        }
        stage('docker build') {
            agent {
                docker {
                    image 'docker'
                    args '-v /var/run/docker.sock:/var/run/docker.sock -u 0:0'
                }
            }
            when { branch 'master' }
            steps {
                sh "docker login -u ${DOCKER_USERNAME} -p ${DOCKER_PASSWORD} ${DOCKER_REGISTRY}"
                sh "docker build -t ${DOCKER_REGISTRY}/starterkit ."
                sh "docker push ${DOCKER_REGISTRY}/starterkit"
            }
        }
        stage('deploy') {
            agent any
            when { branch 'master' }
            steps {
                sh "curl -s -o /dev/null -i \"https://jenkins.wizaplace.com/buildByToken/buildWithParameters?job=DEPLOY_k8s_starterkit&token=${DEPLOY_STARTERKIT_TOKEN}&VERSION=${GIT_COMMIT}\""
            }
        }
    }
    post {
        failure {
            slackSend channel: "#ci-errors", color: "danger", message: "${env.JOB_NAME} - ${env.BUILD_DISPLAY_NAME} failure (<${env.BUILD_URL}|Open>)"
            emailext subject: "Failed: ${env.JOB_NAME} - ${env.BUILD_DISPLAY_NAME}", attachLog: true, recipientProviders: ["developers"], body: "${JELLY_SCRIPT,template="html"}"
        }
    }
    environment {
        DOCKER_REGISTRY = credentials('546dd443-92b3-4712-9fa4-58d1546ff464')
        DOCKER_USERNAME = credentials('fe8f6ec1-fbd7-455a-bf0e-992f0562da61')
        DOCKER_PASSWORD = credentials('0455ac3f-611f-4073-b626-5383520c29d2')
        DEPLOY_STARTERKIT_TOKEN = credentials('c7951c3b-2c26-4fcf-8a92-ea4d1e8b6f96')
    }
}
