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
                withCredentials([string(credentialsId: '8ffb1dc7-4858-4c4a-ac9e-0a1d655a3b59', variable: 'GITHUB_TOKEN')]) {
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
                    args '-u 0:0'
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
                        junit 'coke-result.xml'
                    },
                    'stan': {
                        sh 'make stan-ci'
                    },
                    'test': {
                        sh 'make test-ci'
                        junit 'phpunit-result.xml'
                        step([
                            $class: 'CloverPublisher',
                            cloverReportDir: './',
                            cloverReportFileName: 'clover.xml'
                        ])
                    }
                )
            }
        }
    }
}
