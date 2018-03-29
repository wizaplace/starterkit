pipeline {
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
        stage('check') {
            agent {
                docker {
                    image 'php:7.1'
                    args '-u 0:0'
                }
            }
            steps {
                parallel(
                    'lint': {
                        sh 'make -j lint-ci'
                    },
                    'stan': {
                        sh 'make stan-ci'
                    },
                )
            }
            post {
                always {
                    archiveArtifacts allowEmptyArchive: true, artifacts: 'phpcs-checkstyle.xml'
                    archiveArtifacts allowEmptyArchive: true, artifacts: 'phpstan-checkstyle.xml'
                    withCredentials([string(credentialsId: 'e18082c0-a95c-4c22-9bf5-803fd091c764', variable: 'GITHUB_TOKEN')]) {
                        step([
                            $class: 'ViolationsToGitHubRecorder',
                            config: [
                                gitHubUrl: 'https://api.github.com/',
                                repositoryOwner: 'wizaplace',
                                repositoryName: 'starterkit',
                                pullRequestId: "${env.CHANGE_ID}",
                                useOAuth2Token: true,
                                oAuth2Token: "$GITHUB_TOKEN",
                                useUsernamePassword: false,
                                useUsernamePasswordCredentials: false,
                                usernamePasswordCredentialsId: '',
                                createCommentWithAllSingleFileComments: false,
                                createSingleFileComments: true,
                                commentOnlyChangedContent: true,
                                minSeverity: 'INFO',
                                violationConfigs: [
                                    [ pattern: '.*/.*-checkstyle\\.xml$', parser: 'CHECKSTYLE', reporter: 'Checkstyle' ],
                                ]
                            ]
                        ])
                    }
                }
            }
        }
        stage('docker build') {
            agent {
                docker {
                    image 'docker'
                    args '-v /var/run/docker.sock:/var/run/docker.sock -u 0:0'
                }
            }
            // when { branch 'master' }
            steps {
                sh "docker login -u ${DOCKER_USERNAME} -p ${DOCKER_PASSWORD} ${DOCKER_REGISTRY}"
                sh "docker build -t ${DOCKER_REGISTRY}/starterkit ."
                sh "docker push ${DOCKER_REGISTRY}/starterkit"
            }
        }
    }
    environment {
        DOCKER_REGISTRY = credentials('546dd443-92b3-4712-9fa4-58d1546ff464')
        DOCKER_USERNAME = credentials('fe8f6ec1-fbd7-455a-bf0e-992f0562da61')
        DOCKER_PASSWORD = credentials('0455ac3f-611f-4073-b626-5383520c29d2')
    }
}
