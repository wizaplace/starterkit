


#--------
# Helpers
#--------

is-vagrant-user:
	@user=`whoami` ; \
	if test $$user != "vagrant" ; \
	then \
		echo "You must run this command in Vagrant environment"; \
		exit 1; \
	fi

is-not-vagrant-user:
	@user=`whoami` ; \
	if test $$user = "vagrant" ; \
	then \
		echo "You must not run this command in Vagrant environment"; \
		exit 1; \
	fi

#----------------------------------------------------------------
# Tache unique pour installer l'environnement de dev avec Vagrant
#----------------------------------------------------------------

dev-from-scratch: is-not-vagrant-user
	# Lancement de Vagrant
	@vagrant up
	@vagrant ssh --command "cd /vagrant && make install"


#-------------------------------------------------------------
# Tache d'intallation/initialisation de l'environnement de dev
#-------------------------------------------------------------

install: is-vagrant-user
	composer install --no-scripts
	cp app/config/parameters.yml.dist app/config/parameters.yml
	npm install
	make assets

assets: is-vagrant-user
	gulp deploy
