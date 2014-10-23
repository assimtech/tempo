# Overview

Tempo is a php tool for scripting the execution of commands on local and remote hosts. Tempo requires you have php-cli
and an ssh client installed on host you are running it from. Remote nodes do not need anything apart from an ssh server.

Tempo allows you to express some sets of commands to run using a few simple definitions.  It leverages native ssh client
and therefore does not depend on any special php libraries.  This also means it adopts the same authentication setup as
your normal shell so if you use an agent or keychain, tempo will use it.

Tempo definitions are written in PHP because it's a language you are hopefully already familiar with. It also gives you
a lot of power to do all kinds of advanced things to achieve your task.

Some of the [Tasks](docs/06-Tasks.md) you may want to do will already be built and can just be used with minimal effort.


## Topics

* [Overview](01-Overview.md)
* [Installation](02-Installation.md)
* [Environments](03-Environments.md)
* [Nodes](04-Nodes.md)
* [Commands](05-Commands.md)
* [Tasks](06-Tasks.md)
* [Contributing](07-Contributing.md)


## Design

Tempo is an executable which searches for a file named `tempo.php` in the current working directory which constructs and
returns an `Assimtech\Tempo\Definition` that defines your available commands.

The main design objectives are:

* Easy setup
* Easy to understand
* Minimal dependencies
* Capable of complex tasks


## Authentication

Since tempo uses ssh and all the environmental variables from your current session, it will leverage any agent or
keychain you have set up. We recommend using tempo with agent forwarding if you expect to your remote ssh based commands
to work without asking you for your password.

* [ssh-agent](http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man1/ssh-agent.1)
* [pageant](http://the.earth.li/~sgtatham/putty/0.63/htmldoc/Chapter9.html#pageant)


### Connection sharing

To make successive remote commands run over a single ssh connection, tempo makes use of a
[ControlMaster](http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man5/ssh_config.5) connection. This means a
single connection is established and successive commands go over this connection. This is done by storing a socket file
in `~/.ssh/tempo_*`. Tempo keeps this socket open for a period of time and closes it once the node is destroyed.

For advanced remote node configuration options, please see [Nodes](04-Nodes.md).
