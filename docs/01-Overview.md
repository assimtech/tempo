# Overview

Tempo is a php tool for scripting the execution of commands on local and remote hosts. Tempo requires you have php-cli
and an ssh client installed on host you are running it from. Remote nodes do not need anything apart from an ssh server.


## Advanced topics

* [Overview](01-Overview.md)
* [Installation](02-Installation.md)
* [Environments](03-Environments.md)
* [Nodes](04-Nodes.md)
* [Commands](05-Commands.md)
* [Tasks](06-Tasks.md)
* [Contributing](07-Contributing.md)


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
in `~/.ssh/tempo_ctlmstr_*`. Tempo keeps this socket open for up to 10 minutes and closes it once the node is destroyed.

For advanced remote node configuration options, please see [Nodes](04-Nodes.md).