pipeline {
    agent any

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
                sh 'make npm-install assets'
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
                        sh 'make lint-ci'
                    },
                    'stan': {
                        sh 'make stan-ci'
                    },
                    'test': {
                        sh 'make test-phpunit-ci'
                    }
                )
            }
            post {
                always {
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
                                createCommentWithAllSingleFileComments: true,
                                createSingleFileComments: true,
                                commentOnlyChangedContent: true,
                                minSeverity: 'INFO',
                                violationConfigs: [
                                    [ pattern: '.*/coke-checkstyle\\.xml$', reporter: 'CHECKSTYLE' ],
                                ]
                            ]
                        ])
                    }
                    junit 'phpunit-result.xml'
                    step([
                        $class: 'CloverPublisher',
                        cloverReportDir: './',
                        cloverReportFileName: 'clover.xml'
                    ])
                }
            }
        }
        stage('behat') {
            agent {
                dockerfile {
                    dir 'tests/behat/docker'
                    args '-u 0:0'
                }
            }
            steps {
                sh '/entrypoint.sh'
            }
            post {
                always {
                    junit 'behat-result/*.xml'
                }
            }
        }
    }

    post {
        always {
            archiveArtifacts allowEmptyArchive: true, artifacts: 'var/logs/test.log'
            archiveArtifacts allowEmptyArchive: true, artifacts: 'var/screenshots/**/*.png'
        }
    }
}
