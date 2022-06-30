# README #

Amos Privileges module

### What is this repository for? ###

This repository is used to allow users with admin role to view and manage user privileges (rbac roles and permission and customized privileges eg.cwh if present)

### How do I get set up? ###

Download with composer, add in your composer json:

```
"open20/amos-privileges": "dev-master",
```

Enable the Privileges modules in modules-amos.php, add :
```
'privileges' => [
    'class' => 'open20\amos\privileges\AmosPrivileges',
]
```

add privileges migrations to console modules (console/config/migrations-amos.php):
```
'@vendor/open20/amos-privileges/src/migrations',
```

it is possible to override some module properties: 

    /**
     * @var array roles that must not be considered by Privileges module
     */
    public $blackListRoles = ['AMMINISTRATORE_CRTT'];
    /**
     * @var array if set, consider only the roles in this list
     */
    public $whiteListRoles = [];
    /**
     * @var array array of modules for which privileges are not considered
     */
    public $blackListModules = ['inforeq'];
    /**
     * @var array list of platform roles (not from a single plugin) - override this if necessary
     */
    public $platformRoles = ['ADMIN', 'BASIC_USER'];

override of the first property example:
```
'privileges' => [
    'class' => 'open20\amos\privileges\AmosPrivileges',
    'blackListRoles' => [ 'ROLEA' , 'ROLEB' ]
]
```
