<?php

/*
    Created: Moheb, June 16th, 2020
    Updated: Moheb, June 22nd, 2020
    Updated: Moheb, July 1st, 2020
    Updated: Moheb, August 7th, 2020
*/

/*
    => createDirectory($foldername, $directory, $permission)
        Creates a new folder in the specified directory with the specified permission. The specified directory must be withing the project directory.
    => emptyFolder($directoryPath) 
    => deleteFile($directoryPath) 

    USAGE:
    $foldername: Name of folder to be created. (nonempty string)
    $directory: Name of the directory to create the new folder inside. (nonempty string)
    $permission: Newly created folder read, write and execute permissions (3-4 digits positive integer)

    IF $directory is unspecified, it defaults to the applications folder.
    IF $permission is unspecified, it defaults to 777, (a+rwx)

    Examples:
    1. To create a folder called myfolder inside the application folder with 777 permissions:
        createDirectory("myfolder");
        OR
        createDirectory("myfolder", "");
        OR
        createDirectory("myfolder", "", 777);

    2. To create a folder called myfolder inside the assets folder with permissions 665:
        createDirectory("myfolder", "assets", 665);

        (same goes for any folder directly inside the project root directory: application, system, ... etc)

    3. To create a folder called myfolder inside the system/core folder with permissions 333:
        createDirectory("myfolder", "system/core", 333);

        (same goes for any full path directory inside the project root directory: system/core/compact ... etc)

    4. To create a folder called myfolder inside the project root directory with permissions 775:
        createDirectory("myfolder", "/", 775);

    5. Changes the permissions for myfolder in the assets folder to 775
        changeDirectoryPermissions(APPPATH."../assets/myfolder", 775)
        
    6. Creates a folder with the name myfolder in the applications diretory with 777 permissions.
        createDirectory("myfolder")
        
    7. Creates a folder with the name myfolder in the assets folder relative to the application directory
        with 777 permissions.
        createDirectory("myfolder", APPPATH."../assets")
    
    8. Similar to Example 6.
        createDirectory("myfolder", ""); 
        
    9. Creates a folder with the name myfolder in the applications directory with 775 permissions.
        createDirectory("myfolder", "", 775)
        
    10. Creates a folder with the name myfolder in the assets folder relative to the application directory
        with 555 permissions.
        createDirectory("myfolder", APPPATH."../assets", 555) 
    
    11. Empties all files inside the js folder
        emptyFolder('assets/js/')
        
    12. Returns an error message because var/www/ is not inside the project directory.
        emptyFolder('var/www/')
        
    13. Deletes the clipboard.min.js library in assets/js/essential.
         deleteFile('assets/js/essential/clipboard.min.js')
    
    14. Does not delete somefile.txt even if it exists because var/www is not inside the project root directory. 
         deleteFile('var/www/somefile.txt')
        
*/

#[AllowDynamicProperties]
class DirectoryManager {

    private $ci;
    private $realPath;

	public function __construct() {
        $this->ci =& get_instance();
        $this->realPath = realpath(APPPATH."../");
    }

    /*
        Checks if provided directory is a project directory.
    */
    public function isValidProjectDirectory($directory) {
        if (strlen($directory) < strlen($this->realPath)) {
            return false;
        }
        
        $basepath = substr($directory, 0, strlen($this->realPath));
        return $basepath == $this->realPath;
    }

    // Checks if directory permissions are valid (Helper function)
    public function checkPermissions(&$permission) {
        $response = array();
        // Check if permission is an integer.
        if (!is_int($permission)) {
            $response["Message"] = "Permission must be an integer.";
            return $response;
        } 

        // Check if permission is a positive integer.
        if ($permission <= 0) {
            $response["Message"] = "Permission must be a positive integer";
            return $response;
        }

        // Convert permission to string.
        $permission_str = strval($permission);
        $permission_len = strlen($permission_str);

        // Check if permission octals are 1 to 4.
        if ($permission_len > 4) {
            $response["Message"] = "Permission may not exceed 4 octals";
            return $response;
        }

        if (!octdec($permission)) {
            $response["Message"] = "Permission octal must be from 0 to 7";
            return $response;
        }

        $permission = octdec($permission);
        return true;
    }
    
    /*
    Changes the permissions for the specified directory with the new permission

    Contract:
    Args:
    string, positive 1 to 4 digits integer
    
    Return:
    oneof({Message: response message}, true)

    */

    public function changeDirectoryPermissions($directoryPath, $permission) {
        $response = array();

        $response["Status"] = "Error";
        // Default to APPPATH when directory is not specified.
        if ($directoryPath == "") {
            $directoryPath = APPPATH;
        }

        if ($directoryPath == "/") {
            $directoryPath = APPPATH . "../";
        }

        $directoryPath = realpath($directoryPath);

        // Check if directory is valid.
        if (!is_dir($directoryPath)) {
            $response["Message"] = "Invalid directory";
            return $response;
        }

        // Check if directory is inside the project directory.
        if (!$this->isValidProjectDirectory($directoryPath)) {
            $response["Message"] = "Directory must be within the project directory.";
            return $response;
        }

        $directoryPath = realpath($directoryPath);
        // Check if the specified directory to create the folder within exists.
        if (!file_exists($directoryPath)) {
            $response["Message"] = "Failed to find directory " . $directoryPath;
            return $response;
        }

        // Check permissions.
        $permission_check = $this->checkPermissions($permission);
        if ($permission_check !== true) {
            return $permission_check;
        }

        // Check if chmod fails.
        if (!chmod($directoryPath, $permission)) {
            $response["Message"] = "Failed to change permissions for " . $directoryPath;
            return $response;
        }

        // Changed permissions successfully.
        $response["Message"] = "Changed permissions successfully to " . $permission . " for " . $directoryPath;
        $response["Status"] = "Success";
        return $response;
    }

    /*
    Creates a new directory folder in the given path, with the given permissions.

    Contract:
    Args (One of): 
    1. nonempty string
    2. nonempty string, string
    2. nonempty string, string, positive 1 to 4 digits integer
    
    Return:
    {Status: oneof("Success", "Error"), Message: response message}

    */
    public function createDirectory($foldername, $directory = APPPATH, $permission = 777) {

        // Function returns response on Success or Error
        $response = array();
        $response["Status"] = "Error";

        // Check if foldername is a string.
        if (!is_string($foldername)) {
            $response["Message"] = "Folder name must be a string.";
            return $response;
        }
        // Check if directory is a string.
        if (!is_string($directory)) {
            $response["Message"] = "Directory must be a string.";
            return $response;
        }
        // Check if folder name is provided.
        if ($foldername == "") {
            $response["Message"] = "Directory name needs to be specified.";
            return $response;
        }

        // Default to APPPATH when directory is not specified.
        if ($directory == "") {
            $directory = APPPATH;
        }

        if ($directory == "/") {
            $directory = APPPATH . "../";
        }

        $directory = realpath($directory);

        // Check if directory is valid.
        if (!is_dir($directory)) {
            $response["Message"] = "Invalid directory";
            return $response;
        }

        // Check if directory is inside the project directory.
        if (!$this->isValidProjectDirectory($directory)) {
            $response["Message"] = "Directory must be within the project directory.";
            return $response;
        }

        // Check if the path is forward-slash terminated then concatenate the foldername if it is,
        // otherwise, concatenate the foldername with a leading forward slash.
        if (substr($directory, -1) == '/') {
            $fullpath = $directory . $foldername;
        } else {
            $fullpath = $directory . '/' . $foldername;
        }

        // Check if the specified directory to create the folder within exists.
        if (!file_exists($directory)) {
            $response["Message"] = "Failed to find directory " . $directory;
            return $response;
        }

        // Check if the folder already exists.
        if (file_exists($fullpath)) {
            $response["Message"] = $fullpath . " already exists";
            return $response;
        }
        
        // Check permissions
        $permission_check = $this->checkPermissions($permission);
        if ($permission_check !== true) {
            return $permission_check;
        }

        $old_umask = umask(0);
        // Check if mkdir fails.
        if (!mkdir($fullpath, $permission, true)) {
            $response["Message"] = "Failed to create " . $fullpath;
            return $response;
        }
        umask($old_umask);

        // Directory is successfully created.
        $response["Message"] = $fullpath . " created successfully with permission " . $permission;
        $response["Status"] = "Success";
        return $response;
    }

    /**
     * Created: Moheb, August 7th, 2020
     * 
     * Empties all FILES inside a directory IF the directory exists inside the root project directory.
     * Returns a success message on success; otherwise, returns a failure message.
     * 
     * @param string $directoryPath
     * @return array
     */
    public function emptyFolder($directoryPath) {
        $response = array('Status' => 'Failure', 'Message' => 'Directory does not exist');
        if (!file_exists(realpath(APPPATH . "../" . $directoryPath))) {
            return $response;
        }
        $files = glob(APPPATH . "../" . $directoryPath . '*');
        foreach($files as $file) {
            if (is_file($file)) {
                if (!unlink($file)) {
                    $response['Message'] = 'Failed to remove file: ' . $file;
                    return $response;
                }
            }
        }
        return array('Status' => 'Success', 'Message' => 'Folder emptied successfully');
    }

    /**
     * Created: Moheb, August 10th, 2020
     * 
     * Deletes the file specified by directoryPath inside the project root directory.
     * Returns a success message on success; otherwise, returns a failure message.
     * 
     * 
     * @param string $directoryPath
     * @return array
     */
    public function deleteFile($directoryPath) {
        if (!unlink($directoryPath)) {
            return array('Status' => 'Failure', 'Message' => 'Failed to delete directory: ' . $directoryPath);
        };
        return array('Status' => 'Success', 'Message' => 'Directory deleted successfully.');
    }
}