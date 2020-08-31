<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Facades\Module;
use \Nuwave\Lighthouse\Schema\Source\SchemaStitcher;
use File;

class generateGraphQL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:graphql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->readFiles();
        return 0;
    }
    /**
     *  1- go inside models and grap all graphql files
     *      - read, then write them back to main app gql
     * 2- go to main app gql files grap them and do the same from befor.
     * 
     */
    //  get modules
    // get customs modules
    protected $gqlNamespaces = [
        'queries' => 'GraphQL\\Queries',
        'mutations' => 'GraphQL\\Mutations',
        'subscriptions' => 'GraphQL\\Subscriptions',
        'interfaces' => 'GraphQL\\Interfaces',
        'unions' => 'GraphQL\\Unions',
        'scalars' => 'GraphQL\\Scalars',
        'directives' => 'GraphQL\\Directives',
        'schema' => 'GraphQL\\Schema',

    ];
    private function readFiles()
    {
        dump(exec("whoami"));
        $gqlPath = '/graphQL';
        $m = Module::allEnabled();
        foreach ($m as $module) {
            foreach ($this->gqlNamespaces as $key => $folder) {
               
                $_path = $module->getPath() . '\\' . $folder;
                $_path_in_app = 'App\\' . $folder;

                // ignore empty folders
                if (!is_dir($_path) || count(glob($_path . "/*")) === 0) {
                    continue;
                }

                // foreach (glob($_path . "/*") as $file) {
                //     echo "file: " . $file;
                //     echo exec('ln -s ' . $file . ' ' . $_path_in_app . '/' . $file);
                //     if (is_file($file)) {
                //     }
                // }
                $this->copyRecursive($_path, $_path_in_app);
                if ($key === 'schema') {

                    var_dump("folder: " . $key);
                }
                // !! permmissons problem
                $this->readFile($_path);
                // File::copy($_path, $_path_in_app);
            }  // $this->sync($_path, $_path_in_app);
        }
    }
    function readFile($file)
    {
        $current = file_get_contents($file);
        // add warning
        var_dump($file);
    }
    function file_force_contents( $fullPath, $contents, $flags = 0 ){
        $parts = explode( '/', $fullPath );
        array_pop( $parts );
        $dir = implode( '/', $parts );
       
        if( !is_dir( $dir ) )
            mkdir( $dir, 0777, true );
       
        file_put_contents( $fullPath, $contents, $flags );
    }
    
    // file_force_contents( ROOT.'/newpath/file.txt', 'message', LOCK_EX );

    function schema($_schema)
    {
        $stitcher = new SchemaStitcher($_schema);
        return $stitcher->getSchemaString();
    }
    function symlink_force($source, $dest)
    {
        if (file_exists($dest)) {
            unlink($dest);
        }
        symlink($source, $dest);
    }
    private function copyRecursive($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        echo "Source: " . $source . " Des: " . $dest;
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            if (is_file($dest)) {
                unlink($dest);
            }
            return copy($source, $dest);
        }
        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }
        // Loop through the folder
        $dir = dir($source);
        while (false !== ($entry = $dir->read())) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            // Deep copy directories
            $this->copyRecursive("{$source}/{$entry}", "{$dest}/{$entry}");
        }
        // Clean up
        $dir->close();
        return true;
    }
    function recurse_copy($src, $dst)
    {

        $dir = opendir($src);
        @mkdir(dirname($dst));

        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . DS . $file)) {
                    recurse_copy($src . DS . $file, $dst . DS . $file);
                } else {
                    copy($src . DS . $file, $dst . DS . $file);
                }
            }
        }
        closedir($dir);
    }
    function sync()
    {

        $files = array();
        $folders = func_get_args();

        if (empty($folders)) {
            return FALSE;
        }

        // Get all files
        foreach ($folders as $key => $folder) {
            // Normalise folder strings to remove trailing slash
            $folders[$key] = rtrim($folder, DIRECTORY_SEPARATOR);
            $files += glob($folder . DIRECTORY_SEPARATOR . '*');
        }

        // Drop same files
        $uniqueFiles = array();
        foreach ($files as $file) {
            if (!is_readable($file)) {
                echo $file . ":  Permission denied";
                return;
            }
            $hash = md5_file($file);

            if (!in_array($hash, $uniqueFiles)) {
                $uniqueFiles[$file] = $hash;
            }
        }


        // Copy all these unique files into every folder
        foreach ($folders as $folder) {
            foreach ($uniqueFiles as $file => $hash) {
                copy($file, $folder . DIRECTORY_SEPARATOR . basename($file));
            }
        }
        return TRUE;
    }
}
