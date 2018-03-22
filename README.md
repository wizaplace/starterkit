# Wizaplace front-office StarterKit

The StarterKit is a template web application for creating an e-commerce front-office for Wizaplace.

It is based on PHP 7.1, Symfony 3 and [our PHP SDK](https://github.com/wizaplace/wizaplace-php-sdk). The front-office connects to Wizaplace through the API ([API documentation](https://sandbox.wizaplace.com/api/v1/doc/)).

## Architecture

![](http://i.imgur.com/uWzynHK.png)

The StarterKit project is meant to be cloned (forked on GitHub for example) for each new front-office project. The cloned version can then be customized to fit the target design.

## Features

The StarterKit is a starting point for your front-office. Some specific pages and features may be missing in the StarterKit and may require to create or modify the Symfony routes, controlers and views to add them.

## Setup

### Using Vagrant

**This is the recommended solution.**

Requirements:

- [VirtualBox](https://www.virtualbox.org/wiki/Downloads),
- [Vagrant 1.9.5](https://releases.hashicorp.com/vagrant/1.9.5/),
- [Landrush](https://github.com/vagrant-landrush/landrush) and [Vagrant-cachier plugin](https://github.com/fgrehm/vagrant-cachier) plugins: `vagrant plugin install landrush vagrant-cachier`,
- NFS on Linux: `sudo apt-get install nfs-common nfs-kernel-server`.
- dnsmasq on Linux: `sudo apt-get install dnsmasq`

If you have not done this already, you need to create a SSH key and configure it into your GitHub account: https://help.github.com/articles/adding-a-new-ssh-key-to-your-github-account/

Store your SSH key in the SSH agent:

- MacOS: `ssh-add -K`
- Linux: `ssh-add`

Clone the project and install it:

```
$ git clone git@github.com:wizaplace/starterkit.git
$ cd starterkit/
$ make dev-from-scratch
```

The website is now reachable at [http://demo.loc/](http://demo.loc/).  
You can have access to the profiler bar at [http://demo.loc/app_dev.php](http://demo.loc/app_dev.php).

At any time you can rebuild everything by running `make dev-from-scratch` again.

#### Customization

You can customize your local Vagrant configuration by creating a file named `Vagrantfile.local`, for example:

```ruby
Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.vm.provider "virtualbox" do |v|
        v.memory = 1536
        v.cpus = 2
        v.customize ['modifyvm', :id, '--cableconnected1', 'on']
    end

    config.vm.provision "file", source: "~/.oh-my-zsh/themes/honukai.zsh-theme", destination: "/home/vagrant/.oh-my-zsh/themes/honukai.zsh-theme"
    config.vm.provision "file", source: "~/vagrant-zshrc", destination: "/home/vagrant/.zshrc"
    config.vm.provision "shell", path: "~/vagrant-provision.sh"

    config.vm.network 'forwarded_port', guest: 3306, host: 3306
end
```

### Without Vagrant

This method can be used when Vagrant cannot be made to work.

Requirements:

- PHP 7.1
- Composer
- NPM
- Gulp

```
make install
# Run the built-in webserver
bin/console server:run
```

The application should be available at http://localhost:8000/

## Development

To compile assets, simply run `gulp`. Gulp will watch changes and recompile Javascript, CSS, etc. automatically.

### Integration

Here are our integration guidelines: [wizaplace/integration-guidelines](https://github.com/wizaplace/integration-guidelines).

A minimal stylesheet (`src/AppBundle/Resources/public/style/wizaplace/demo.scss`) is provided for demo purpose but must be removed before starting the integration phase as it can cause style conflicts with your own styles.
