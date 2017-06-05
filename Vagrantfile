VAGRANTFILE_API_VERSION = '2'

Vagrant.require_version ">= 1.8.0"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    # Box
    config.vm.box = "kuikui/modern-lamp"
    config.vm.box_version = ">= 3.0.1"

    config.vm.provider "virtualbox" do |v|
      v.memory = 1536
      v.customize ['modifyvm', :id, '--cableconnected1', 'on']
    end

    # Hostname
    # Sur chrome, les domaines en .dev provoquent parfois des ERR_ICANN_NAME_COLLISION
    config.vm.hostname = 'demo.loc'
    if Vagrant.has_plugin?('landrush')
        config.landrush.enabled            = true
        config.landrush.tld                = config.vm.hostname
        config.landrush.host               'demo.loc'
        config.landrush.guest_redirect_dns = false
    end

    # Network
    config.vm.network 'private_network', type: 'dhcp'
    # Nécessaire sous windows : à décommenter
    # config.vm.network 'forwarded_port', guest: 80, host: 8888

    # SSH
    config.ssh.forward_agent = true

    # Folders
    # A commenter sous windows (le partage par defaut est déjà au bon endroit donc pas besoin de le preciser
    # et les mount_options définies ci-dessous sont incompatibles)
    config.vm.synced_folder '.', '/vagrant', type: 'nfs', mount_options: ['nolock', 'actimeo=1', 'fsc']

    # Provisioning
    if File.exists?(ENV['HOME'] + "/.gitconfig")
        config.vm.provision "file", source: "~/.gitconfig", destination: "/home/vagrant/.gitconfig"
    end
    config.vm.provision "shell", path: "vagrant/provision.sh", keep_color: true, privileged: false
end

local_vagrantfile = File.expand_path('../Vagrantfile.local', __FILE__)
load local_vagrantfile if File.exists?(local_vagrantfile)
