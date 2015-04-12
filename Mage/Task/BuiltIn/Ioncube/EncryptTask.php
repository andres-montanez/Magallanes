<?php
/*
* This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Task\BuiltIn\Ioncube;

use Mage\Task\AbstractTask;
use Mage\Console;
use Mage\Task\ErrorWithMessageException;

/**
 * This allows intergrating IonCube PHP
 * encoder into deployment system
 * It takes the source path renames
 * it to .raw creates a fresh source
 * path and runs ioncube encoder placing
 * encoded files into source folder.
 * Afterwards it removes the old .raw
 * folder This means that we dont have
 * to change the source path within the
 * main scripts and allows the built
 * in rsync and other tasks to operate
 * on the encrypted files.
 *
 * IonCube PHP Encoder can be downloaded from
 * http://www.actweb.info/ioncube.html
 *
 * Example enviroment.yaml file at end
 *
 *
 * (c) ActWeb 2013
 * (c) Matt Lowe (marl.scot.1@googlemail.com)
 *
 * Extends Magallanes (c) Andrés Montañez <andres@andresmontanez.com>
 *
 */
class EncryptTask extends AbstractTask
{
    /**
     * Name of the task
     *
     * @var string
     */
    private $name = 'IonCube Encoder';

    /**
     * Array of default Ioncube
     * options
     *
     * @var array
     */
    private $default = array();

    /**
     * Array of YAML Ioncube
     * options
     *
     * @var array
     */
    private $yaml = array();

    /**
     * Array of file Ioncube
     * options (taken from additional
     * external config file if supplied)
     *
     * @var array
     */
    private $file = array();

    /**
     * Source directory as used by
     * main scripts
     *
     * @var string
     */
    private $source = '';

    /**
     * Name of tempory folder
     * for source code to be moved
     * to.
     *
     * @var string
     */
    private $ionSource = '';

    /**
     * How the default/yaml/project
     * files interact with each other
     *
     * @var string
     */
    private $ionOverRide = '';

    /**
     * Config options from the
     * enviroment config file
     *
     * @var array
     */
    private $mageConfig = array();

    /**
     * Final version of the IonCube
     * options, after merging all
     * sources together
     *
     * @var array
     */
    private $ionCubeConfig = array();

    /**
     * Default encoder version to use
     * for the ioncube encoder
     *
     * @var string
     */
    private $encoder = 'ioncube_encoder54';

    /**
     * Name of tempory IonCube Project
     * file, used when running encoder
     *
     * @var string
     */
    private $projectFile = '';

    /**
     * If true then run a check on
     * all files after encoding and
     * report which ones are not encoded
     * if any are found to not be encoded
     * then prompt if we should continue
     * with the process
     * If not then clean up the temp files
     * and exit with cancled code.
     *
     * @var bool
     */
    private $checkEncoding = false;

    /**
     * List of file extensions to exclude
     * from encrypted/encoded test
     *
     * @var array
     */
    private $checkIgnoreExtens = array();

    /**
     * List of paths to exclude from
     * encrypted/encoded test
     * Paths must begin with '/'
     * and are all relative to the
     * project root
     *
     * @var array
     */
    private $checkIgnorePaths = array();

    /**
     * (non-PHPdoc)
     *
     * @see \Mage\Task\AbstractTask::getName()
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Mage\Task\AbstractTask::init()
     */
    public function init()
    {
        // Set the default extensions to ignore
        $this->checkIgnoreExtens = array(
            'jpg',
            'jpeg',
            'png',
            'js',
            'gif',
            'css',
            'ttf',
            'svg',
            'map',
            'ico',

        );
        // Get any options specfic to this task
        $this->mageConfig = $this->getConfig()->environmentConfig('ioncube');
        /*
         * Get all our IonCube config options
         */
        $this->getAllIonCubeConfigs();
        /*
         * get the source code location
         */
        $this->source = $this->getConfig()->deployment('from');
        /*
         * remove trailing slash if present
         */
        if (substr($this->source, -1) == DIRECTORY_SEPARATOR) {
            $this->source = substr($this->source, 0, -1);
        }
        /*
         * Set the name of the folder that the un-encrypted
         * files will be moved into
         */
        $this->ionSource = $this->source . '.raw';
        /*
         * set the filename for the ioncube project build file
         */
        $this->projectFile = $this->source . '.prj';
        /*
         * Check if we have been given an encoder script
         * If not then we will just use the default
         */
        if (isset($this->mageConfig ['encoder'])) {
            $this->encoder = $this->mageConfig ['encoder'];
        }
        /*
         * Check if a differant merge type has been
         * supplied, this defines how the 3 differant
         * config files will be merged together.
         */
        if (isset($this->mageConfig ['override'])) {
            $this->ionOverRide = $this->mageConfig ['override'];
        }
        /*
         * Check if we have been asked to
         * confirm all encodings
         */
        if (isset($this->mageConfig ['checkencoding'])) {
            $this->checkEncoding = true;
        }
        /*
         * Check if we have been passed any extra
         * file extensions to exclude from
         * encrypt/encode file check
         *
         */
        if (isset($this->mageConfig ['checkignoreextens'])) {
            $this->checkIgnoreExtens = array_merge($this->ignoreExtens, $this->mageConfig['ignoreextens']);
        }

        /*
         * Check if we have been passed any extra
        * file paths/files to exclude from
        * encrypt/encode file check
        *
        */
        if (isset($this->mageConfig ['checkignorepaths'])) {
            $this->checkIgnorePaths = array_merge($this->checkIgnorePaths, $this->mageConfig['checkignorepaths']);
        }


        /*
         * now merge all the config options together
         */
        $this->ionCubeConfig = $this->mergeConfigFiles();
    }

    /**
     * This gets all the Ioncube configs
     * Basicly it gets the default options contianed within this script
     * It reads any project options from the enviroment.yaml config file
     * It reads any additional options from external project file if set
     *
     * @return void
     */
    private function getAllIonCubeConfigs()
    {

        /*
         *  Get a set of default IonCube options
         */
        $this->default = $this->getOptionsDefault();
        /*
         * Check if there is a 'project' section,
         * if so then get the options from there
         */
        if (isset($this->mageConfig ['project'])) {
            $this->yaml = $this->getOptionsFromYaml($this->mageConfig ['project']);
        } else {
            $this->yaml = array(
                's' => array(),
                'p' => array()
            );
        }
        /*
         * Check if a seperate projectfile has been specified, and if so
         * then read the options from there.
         */
        if (isset($this->mageConfig ['projectfile'])) {
            $this->file = $this->getOptionsFromFile($this->mageConfig ['projectfile']);
        } else {
            $this->file = array(
                's' => array(),
                'p' => array()
            );
        }
    }

    /**
     * Encrypt the project
     * Steps are as follows :
     * Switch our current source dir to the ioncube srouce dir and create new empty dir to encrypt into
     * Write the IonCube project file (this is the file that controls IonCube encoder)
     * Run IonCube encoder
     * Delete the temporary files that we created (so long as we hadn't set 'keeptemp')
     * Return the result of the IonCube encoder
     *
     * @see \Mage\Task\AbstractTask::run()
     *
     * @return bool
     * @throws \Mage\Task\ErrorWithMessageException
     */
    public function run()
    {
        $this->switchSrcToTmp();
        $this->writeProjectFile();
        $result = $this->runIonCube();
        Console::output("Encoding result :" . ($result ? '<green>OK</green>' : '<red>Bad!</red>') . "\n", 0, 2);
        if (!$result) {
            $this->deleteTmpFiles();
            throw new ErrorWithMessageException('Ioncube failed to encode your project :' . $result);
        }
        if (($this->checkEncoding) && (!$this->checkEncoding())) {
            $this->deleteTmpFiles();
            throw new ErrorWithMessageException('Operation canceled by user.');
        }
        $this->deleteTmpFiles();
        return $result;
    }

    /**
     * Runs through all files in the encoded
     * folders and lists any that are not
     * encoded.  If any are found then prompt
     * user to continue or quit.
     * If user quites, then clean out encoded
     * files, and return true to indicate error
     *
     * @return bool
     */
    private function checkEncoding()
    {
        $src = $this->source;
        // $ask holds flag to indicate we need to prompt the end user
        $ask = false;
        // scan through the directory
        $rit = new \RecursiveDirectoryIterator($src);
        foreach (new \RecursiveIteratorIterator($rit) as $filename => $cur) {
            // get the 'base dir' for the project, eg. relative to the temp folder
            $srcFileName = (str_replace($this->source, '', $filename));
            /*
             * Scan through the ignor directorys array
             * and if it matches the current path/filename
             * then mark the file to be skipped from testing
             */
            $skip = false;
            foreach ($this->checkIgnorePaths as $path) {
                if (fnmatch($path, $srcFileName)) {
                    $skip = true;
                }
            }
            // check if we should test this file
            if (!$skip) {
                // get the file exten for this file and compare to our fileexten exclude array
                $exten = pathinfo($filename, PATHINFO_EXTENSION);
                if (!in_array(strtolower($exten), $this->checkIgnoreExtens)) {
                    // ok, this extension needs to be checked
                    if ($this->checkFileCoding($filename)) {
                        // file was encrypted/encoded
                    } else {
                        // file was not encrypted/encoded
                        Console::output("<blue>File :" . $srcFileName . "</blue> -> <red>Was not encrypted</red>", 0, 1);
                        $ask = true;
                    }
                }
            }
        }
        if ($ask) {
            // ok lets ask the user if they want to procede
            Console::output("\n\nDo you wish to procede (y/N):", 0, 0);
            if (!$this->promptYn()) {
                return false;
            }
        }

        return true;
    }

    /**
     * This simply for user to enter
     * 'y' or 'Y' and press enter, if
     * a single 'y' is not entered then
     * false is returned, otherwise
     * true is returned.
     *
     * @return bool True if 'y' pressed
     */
    private function promptYn()
    {
        $handle = fopen("php://stdin", "r");
        $line = strtolower(fgets($handle));
        if (trim($line) != 'y') {
            return false;
        }
        return true;
    }

    /**
     * This will take the passed file and try to
     * work out if it is an encoded/encrypted
     * ioncube file.
     * It dosent test the file exten, as it
     * expects the calling method to have done
     * that before.
     *
     * @param string $filename Filename, with path, to check
     *
     * @return boolean True if file was encoded/encrypted
     */
    private function checkFileCoding($filename)
    {
        // check to see if this is an encrypted file
        $ioncube = ioncube_read_file($filename, $ioncubeType);
        if (is_int($ioncube)) {
            // we got an error from ioncube, so its encrypted
            return true;
        }
        // read first line of file
        $f = fopen($filename, 'r');
        $line = trim(fgets($f, 32));
        fclose($f);
        // if first line is longer than 30, then this isnt a php file
        if (strlen($line) > 30) {
            return false;
        }
        // if first line starts '<?php //0' then we can be pretty certain its encoded
        if (substr($line, 0, 9) == '<?php //0') {
            return true;
        }
        // otherwise its most likley un-encrypted/encoded
        return false;
    }


    /**
     * Deletes tempory folder and project file
     * if 'keeptemp' is set then skips delete
     * process
     *
     * @throws ErrorWithMessageException If there was a problem with deleting the tempory files
     *
     * @return void
     */
    private function deleteTmpFiles()
    {
        if (isset($this->mageConfig ['keeptemp'])) {
            return;
        }
        Console::log('Deleting tempory files :', 1);
        $ret1 = Console::executeCommand('rm -Rf ' . $this->ionSource, $out1);
        $ret2 = Console::executeCommand('rm ' . $this->projectFile, $out2);
        if ($ret1 && $ret2) {
            return;
        }
        throw new ErrorWithMessageException('Error deleting temp files :' . $out1 . ' : ' . $out2, 40);
    }

    /**
     * Builds the ioncube command
     * and runs it, returning the result
     *
     * @return bool
     */
    private function runIonCube()
    {
        $cli = $this->encoder . ' --project-file ' . $this->projectFile . ' ' . $this->ionSource . DIRECTORY_SEPARATOR . '*';
        $ret = Console::executeCommand($cli, $out);
        return $ret;
    }

    /**
     * Write the config options into
     * a project file ready for use
     * with ioncube cli
     *
     * @throws ErrorWithMessageException If it cant write the project file
     *
     * @return void
     */
    private function writeProjectFile()
    {
        // array used to build config file into
        $out = array();
        // set the project destination
        $out [] = '--into ' . $this->source . PHP_EOL;
        // output the switches
        foreach ($this->ionCubeConfig ['s'] as $key => $value) {
            if ($value) {
                // switch was set to true, so output it
                $out [] = '--' . $key . PHP_EOL;
            }
        }
        // output the options
        foreach ($this->ionCubeConfig ['p'] as $key => $value) {
            // check if we have an array of values
            if (is_array($value)) {
                foreach ($value as $entry) {
                    $out [] = '--' . $key . ' "' . $entry . '"' . PHP_EOL;
                }
            } else {
                // ok just a normal single option
                if (strlen($value) > 0) {
                    $out [] = '--' . $key . ' "' . $value . '"' . PHP_EOL;
                }
            }
        }
        $ret = file_put_contents($this->projectFile, $out);
        if (!$ret) {
            // something went wrong
            $this->deleteTmpFiles();
            throw new ErrorWithMessageException('Unable to create project file.', 20);
        }
    }

    /**
     * This merges the 3 config arrays
     * depending on the 'override' option
     *
     * @return array Final config array
     */
    private function mergeConfigFiles()
    {
        /*
         * Options are the order the arrays are in
         * F - Project File
         * Y - YAML config options (enviroment file)
         * D - Default options as stored in script
         *
         * more options could be added to make this a bit more flexable
         *
         */
        $s = array();
        $p = array();
        switch (strtolower($this->ionOverRide)) {
            case 'fyd' :
                // FILE / YAML / DEFAULT
                $s = array_merge($this->file ['s'], $this->yaml ['s'], $this->default ['s']);
                $p = array_merge($this->file ['p'], $this->yaml ['p'], $this->default ['p']);
                break;

            case 'yfd' :
                // YAML / FILE / DEFAULT
                $s = array_merge($this->yaml ['s'], $this->file ['s'], $this->default ['s']);
                $p = array_merge($this->yaml ['p'], $this->file ['p'], $this->default ['p']);
                break;
            case 'dyf' :
                // DEFAULT / YAML / FILE
                $s = array_merge($this->default ['s'], $this->yaml ['s'], $this->file ['s']);
                $p = array_merge($this->default ['p'], $this->yaml ['p'], $this->file ['p']);
                break;
            case 'd' :
            default :
                // Use defaults only
                $s = $this->default ['s'];
                $p = $this->default ['p'];
                break;
        }
        return array(
            's' => $s,
            'p' => $p
        );
    }

    /**
     * Switches the original source
     * code dir to tempory name
     * and recreates orginal dir
     * allows encryption to be done
     * into source dir, so other functions
     * work without changing
     *
     * @throws ErrorWithMessageException If source dir can't be renamed
     * @throws ErrorWithMessageException If original source dir cant be created
     *
     * @return bool
     */
    private function switchSrcToTmp()
    {
        $ret = Console::executeCommand('mv ' . $this->source . ' ' . $this->ionSource, $out);
        if (!$ret) {
            throw new ErrorWithMessageException('Cant create tmp dir :' . $out, $ret);
        }
        $ret = Console::executeCommand('mkdir -p ' . $this->source, $out);
        if (!$ret) {
            throw new ErrorWithMessageException('Cant re-create dir :' . $out, $ret);
        }
        return true;
    }

    /**
     * Reads a set of options taken from the YAML config
     * Compares keys against the default ioncube settings
     * if a key doesnt appear in the default options, it
     * is ignored
     *
     * return array
     */
    private function getOptionsFromYaml($options)
    {
        $s = array();
        $p = array();
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->default ['s'])) {
                $s [$key] = true;
            }
            if (array_key_exists($key, $this->default ['p'])) {
                $p [$key] = $value;
            }
        }
        return array(
            's' => $s,
            'p' => $p
        );
    }

    /**
     * reads an existing ioncube project
     * file.
     *
     * @param $fileName
     * @return array
     */
    private function getOptionsFromFile($fileName)
    {
        $s = array();
        $p = array();
        $fileContents = file_get_contents($fileName);
        /*
         * split the config file on every occurance of '--' at start of a line
         * Adds a PHP_EOL at the start, so we can catch the first '--'
         */
        $entrys = explode(PHP_EOL . '--', PHP_EOL . $fileContents);
        foreach ($entrys as $line) {
            $line = trim($line);
            if ($line != '') {
                /*
                 *  get position of first space
                 *  so we can split the options out
                 */
                $str = strpos($line, ' ');
                if ($str === false) {
                    /*
                     * Ok, no spaces found, so take this as a single line
                     */
                    $str = strlen($line);
                }
                $key = substr($line, $str);
                $value = substr($line, $str + 1);
                if ((array_key_exists($key, $this->default ['s']))) {
                    /*
                     *  ok this key appears in the switch config
                     *  so store it as a switch
                     */
                    $s [$key] = true;
                }
                if ((array_key_exists($key, $this->default ['p']))) {
                    /*
                     * Ok this key exists in the parameter section,
                     * So store it allong with its value
                     */
                    $p [$key] = $this->splitParam($value);
                }
            }
        }
        return array(
            's' => $s,
            'p' => $p
        );
    }

    /**
     * Takes supplied line and splits it if required
     * into an array
     * returns ether the array, or a plain
     * string.
     * Allows options to be spread accross several lines
     *
     * @param $string String to split
     *
     * @return mixed
     */
    private function splitParam($string)
    {
        $split = explode(PHP_EOL, $string);
        if ($split === false) {
            // nothing found, so return a blank string
            return '';
        }
        if (count($split) == 1) {
            return $split [0];
        } else {
            return $split;
        }
    }

    /**
     * returns an array of default ioncube options
     * This is also used as a 'filter' for the YAML
     * and Config files, if an option hasnt got an
     * entry in this list, then it can not be set
     * via the VAML or Config files
     *
     * @return array
     */
    private function getOptionsDefault()
    {
        $s = array();
        $p = array();
        // Set the switches
        $s ['allow-encoding-into-source'] = false;

        $s ['ascii'] = false;
        $s ['binary'] = true;

        $s ['replace-target'] = true;
        $s ['merge-target'] = false;
        $s ['rename-target'] = false;
        $s ['update-target'] = false;

        $s ['only-include-encoded-files'] = false;

        $s ['use-hard-links'] = false;

        $s ['without-keeping-file-perms'] = false;
        $s ['without-keeping-file-times'] = false;
        $s ['without-keeping-file-owner'] = false;

        $s ['no-short-open-tags'] = false;

        $s ['ignore-strict-warnings'] = false;
        $s ['ignore-deprecated-warnings'] = false;

        $s ['without-runtime-loader-support'] = false;
        $s ['without-loader-check'] = false;

        $s ['disable-auto-prepend-append'] = true;

        $s ['no-doc-comments'] = true;

        // Now set the params
        $p ['encrypt'] [] = '*.tpl.html';
        $p ['encode'] = array();
        $p ['copy'] = array();
        $p ['ignore'] = array(
            '.git',
            '.svn',
            getcwd() . '/.mage',
            '.gitignore',
            '.gitkeep',
            'nohup.out'
        );
        $p ['keep'] = array();
        $p ['obfuscate'] = '';
        $p ['obfuscation-key'] = '';
        $p ['obfuscation-exclusion-file'] = '';
        $p ['expire-in'] = '7d';
        $p ['expire-on'] = '';
        $p ['allowed-server'] = '';
        $p ['with-license'] = 'license.txt';
        $p ['passphrase'] = '';
        $p ['license-check'] = '';
        $p ['apply-file-user'] = '';
        $p ['apply-file-group'] = '';
        $p ['register-autoglobal'] = array();
        $p ['message-if-no-loader'] = '';
        $p ['action-if-no-loader'] = '';
        $p ['loader-path'] = '';
        $p ['preamble-file'] = '';
        $p ['add-comment'] = array();
        $p ['add-comments'] = '';
        $p ['loader-event'] = array();
        $p ['callback-file'] = '';
        $p ['property'] = '';
        $p ['propertys'] = '';
        $p ['include-if-property'] = array();
        $p ['optimise'] = 'max';
        $p ['shell-script-line'] = '';
        $p ['min-loader-version'] = '';

        return array(
            's' => $s,
            'p' => $p
        );
    }
}
