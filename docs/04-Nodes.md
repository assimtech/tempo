# Nodes

A node is a singular host / server, usually defined by a hostname or IP address for the purpose of running commands.
Nodes are typically added to [Environments](03-Environments.md) for use in [Commands](05-Commands.md) and
[Tasks](06-Tasks.md).


## A node is an ArrayObject child

A tempo node extends [ArrayObject](http://php.net/manual/en/class.arrayobject.php), this means you can store properties
on it and access them later.

    $server1['webpath'] = '/var/www/website.example.com';
    $server2['webpath'] = '/opt/website.example.com';

    // ... Later in a task or command definition ...
    doSomethingTo($node['webpath']);


## Local nodes

A local node is used for running commands locally. A local node will always have the name 'localhost'.

    $node = new Assimtech\Tempo\Node\Local();
    echo $node->run('hostname'); // This will print the hostname of the server you are running tempo on


## Remote nodes

A remote node is used for running commands remotely over an ssh connection.

    $node = new Assimtech\Tempo\Node\Remote('server1.example.com');
    echo $node->run('hostname'); // This will print the hostname of server1.example.com


### Configuration

Options can be set on a Remote node through the constructor and will be accessible as array keys.

    $node = new Assimtech\Tempo\Node\Remote(array(
        'user' => 'me',
        'host' => 'server1.example.com',
        'port' => 22, // No default (ssh itself defaults to 22)
    ));


#### host

Mandatory - `string`

This is the hostname or IP used when establishing the ssh connection to the node. This option is configurable in the
shortcut form:

    $node = new Assimtech\Tempo\Node\Remote('server1.example.com');

Or in the full form:

    $node = new Assimtech\Tempo\Node\Remote(array(
        'host' => 'server1.example.com',
    ));


#### user

Optional - `string` (Defaults to the current user on your terminal)

This is the username used when establishing the ssh connection to the node. This option is configurable in the shortcut
form:

    $node = new Assimtech\Tempo\Node\Remote('username@server1.example.com');

Or in the full form:

    $node = new Assimtech\Tempo\Node\Remote(array(
        'user' => 'username',
        'host' => 'server1.example.com',
    ));


#### port

Optional - `integer` (Defaults to 22)

This is the port used when establishing the ssh connection to the node.

    $node = new Assimtech\Tempo\Node\Remote(array(
        'host' => 'server1.example.com',
        'port' => 1234,
    ));


#### ControlMaster configuration

Tempo remote nodes can use a [ControlMaster](http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man5/ssh_config.5)
connection to share an ssh connection for successive commands to the same remote node without re-authenticating etc.
This is enabled by default.

The following options are available:

| Option                       | Description                                                                         |
| :--------------------------- | :---------------------------------------------------------------------------------- |
| useControlMaster             | Use control master connection?                                                      |
| controlPath                  | The socket file to use for connection sharing (see ControlPath in ssh_config(5))    |
| controlPersist               | The policy for leaving the connection open (see ControlPersist in ssh_config(5))    |
| closeControlMasterOnDestruct | Should the control master connection be destroyed when this node is?                |


##### useControlMaster

Optional - `boolean` (Defaults to `true`)

Turns connection sharing via ssh's ControlMaster on or off.


##### controlPath

Optional - `string` (Defaults to `~/.ssh/tempo_ctlmstr_<hash of node properties>`)

The path to the socket file of the ControlMaster connection
See "ControlPath" in <http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man5/ssh_config.5>


##### controlPersist

Optional - `string` (Defaults to `yes` meaning leave the control master connection open)

The policy for leaving the ControlMaster connection open.
See "ControlPersist" in <http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man5/ssh_config.5>

If set to `yes`, we strongly recommend setting "closeControlMasterOnDestruct" to `true`. This will close the
ControlMaster connection when tempo exits.

Other options include times compatible with
[sshd_config(5)](http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man5/sshd_config.5).

E.g. `10m` will mean the ControlMaster will be left open for 10 minutes. This could be used in conjunction with
"closeControlMasterOnDestruct" = `false` to allow successive tempo commands to re-use the first one's connection.

Please be aware, leaving a ControlMaster connection open unnecessarily leaves an attack vector open for anyone with
access to your socket file on the host you are running tempo on (e.g. anyone with root or your user).


##### closeControlMasterOnDestruct

Optional - `boolean` (Defaults to `true`)

This will cause Tempo to attempt to close the ControlMaster connection on exit (if one has been established).
