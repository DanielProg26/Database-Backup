# Database Backup - Restore

Php class with 4 functions.

# First 

> You have to change the ``` bd_model.php ``` Config.

    private $path = ""; // Example: app/lib/backups/
    private $aplication = ''; // Name of your aplication
    private $description = ''; // Description of your database

    // Conection
    private $database = '';
    private $user = 'user';
    private $host = 'localhost';
    private $pass = '';

# Backup

```sh
$m = new BDModel();
$result = $m->backup();
```
> This will return an array with the route

# Restore
This function wait for a param filename
```sh
$m = new BDModel('filename');
$result = $m->restore();
```
> This will return an mensage 

# Delete 

This function will delete a backup
Wait for a param filename
```sh
$m = new BDModel();
$result = $m->delete('filename');
```
> Will return an mensage 


# Backups

>This function will return all backups

```sh
$m = new BDModel();
$result = $m->getBackups();
