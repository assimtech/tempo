# Nodes

A node is a singular host / server, usually defined by a hostname or IP address for the purpose of running commands.
Nodes are typically added to [Environments](03-Environments.md) for use in [Commands](05-Commands.md) and
[Tasks](06-Tasks.md).


## A node is an ArrayObject child

A tempo node extends [ArrayObject](http://php.net/manual/en/class.arrayobject.php), this means you can store properties
on it and access them later.

```php
    $server1['webpath'] = '/var/www/website.example.com';
    $server2['webpath'] = '/opt/website.example.com';

    // ... Later in a task or command definition ...
    doSomethingTo($node['webpath']);
```


## Local nodes

A local node is used for running commands locally. A local node will always have the name 'localhost'.

```php
    $node = new Assimtech\Tempo\Node\Local();
    echo $node->run('hostname'); // This will print the hostname of the server you are running tempo on
```


## Remote nodes

A remote node is used for running commands remotely over an ssh connection.

```php
    $node = new Assimtech\Tempo\Node\Remote('server1.example.com');
    echo $node->run('hostname'); // This will print the hostname of server1.example.com
```


### Configuration

Options can be set on a Remote node through the constructor and will be accessible as array keys.

```php
    $node = new Assimtech\Tempo\Node\Remote(array(
        'ssh' => array(
            'user' => 'me',
            'host' => 'server1.example.com',
            'options' => array( // Any option available to ssh_config(5) can be specified here
                'Port' => 1234,
            ),
        ),
    ));
```


#### ssh host

Mandatory - `string`

This is the hostname or IP used when establishing the ssh connection to the node. This option is configurable in the
shortcut form:

```php
    $node = new Assimtech\Tempo\Node\Remote('server1.example.com');
```

Or in the full form:

```php
    $node = new Assimtech\Tempo\Node\Remote(array(
        'ssh' => array(
            'host' => 'server1.example.com',
        ),
    ));
```


#### ssh user

Optional - `string` (Defaults to the current user on your terminal)

This is the username used when establishing the ssh connection to the node. This option is configurable in the shortcut
form:

```php
    $node = new Assimtech\Tempo\Node\Remote('username@server1.example.com');
```

Or in the full form:

```php
    $node = new Assimtech\Tempo\Node\Remote(array(
        'ssh' => array(
            'user' => 'username',
            'host' => 'server1.example.com',
        ),
    ));
```


#### ssh options

Optional - `array` Any option that ssh takes as `-o option` can be specified here except those included in the ssh
control section below.

See also [ssh_config(5)](http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man5/ssh_config.5)

This is the port used when establishing the ssh connection to the node.

```php
    $node = new Assimtech\Tempo\Node\Remote(array(
        'ssh' => array(
            'host' => 'server1.example.com',
            'options' => array(
                'Port' => 1234,
            ),
        ),
    ));
```


#### ControlMaster configuration

Tempo remote nodes can use a [ControlMaster](http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man5/ssh_config.5)
connection to share an ssh connection for successive commands to the same remote node without re-authenticating etc.
This is enabled by default.

The following ssh control options are available:

| Option                       | Description                                                                         |
| :--------------------------- | :---------------------------------------------------------------------------------- |
| ssh control useControlMaster | Use control master connection?                                                      |
| ssh control ControlPath      | The socket file to use for connection sharing (see ControlPath in ssh_config(5))    |
| ssh control ControlPersist   | The policy for leaving the connection open (see ControlPersist in ssh_config(5))    |
| ssh control closeOnDestruct  | Should the control master connection be destroyed when this node is?                |

```php
    $node = new Assimtech\Tempo\Node\Remote(array(
        'ssh' => array(
            'host' => 'server1.example.com',
            'control' => array(
                'closeOnDestruct' => false,
            ),
        ),
    ));
```

##### ssh control useControlMaster

Optional - `boolean` (Defaults to `true`)

Turns connection sharing via ssh's ControlMaster on or off.


##### ssh control ControlPath

Optional - `string` (Defaults to `~/.ssh/tempo_<node name>`)

The path to the socket file of the ControlMaster connection
See "ControlPath" in [ssh_config(5)](http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man5/ssh_config.5)


##### ssh control ControlPersist

Optional - `string` (Defaults to `10m` meaning leave the control master connection open for up to 10 minutes)

The policy for leaving the ControlMaster connection open.
See "ControlPersist" in [ssh_config(5)](http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man5/ssh_config.5)

If set to `yes`, we strongly recommend setting "closeOnDestruct" to `true`. This will close the
ControlMaster connection when tempo exits. Even with this set, it is possible if php dies, that a control socket could
be left open. We strongly recommend you set this cautiously. Even if your control connection expires halfway through a
tempo command, tempo will attempt to re-establish it.

Other options include times compatible with the TIME FORMATS section of
[sshd_config(5)](http://www.openbsd.org/cgi-bin/man.cgi/OpenBSD-current/man5/sshd_config.5)

E.g. `10m` will mean the ControlMaster will be left open for 10 minutes. This could be used in conjunction with
"closeOnDestruct" = `false` to allow successive tempo commands to re-use the first one's connection.

Please be aware, leaving a ControlMaster connection open unnecessarily leaves an attack vector open for anyone with
access to your socket file on the host you are running tempo on (e.g. anyone with root or your user).


##### ssh control closeOnDestruct

Optional - `boolean` (Defaults to `true`)

This will cause Tempo to attempt to close the ControlMaster connection on exit (if one has been established).

If you are running concurrent `tempo` commands from the same host, you might consider disabling this to make all the
commands share a single connection to each host.
