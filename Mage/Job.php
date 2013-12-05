<?php

namespace Mage;

class Job {

    const TIMEOUT_PRECISION = 0.2;

    protected $process;
    protected $pipes = [];
    public $stdout = [];
    public $stderr = [];
    public $status;
    public $command;
    public $exitcode;

    const EXIT_CODE_STREAM = 3;

    public static function run($command, $showErrors=false, $verbose=false, $showCommands=false)
    {
        if ($verbose) Console::output("\n<yellow>".$command."</yellow>\n");
        if ($showCommands) Console::output("\n<yellow>".$command."</yellow>\n");
        
        Console::log('---------------------------------');
        Console::log('---- Executing: $ ' . $command);
        
        $j = new Job($command);
        while ($j->isRunning()) {
            list($o, $e) = $j->readStreams(false);
            if ($showErrors && !empty($e)) Console::output("<red>$e</red>");
            if ($verbose && !empty($o)) Console::output("<dark_gray>$o</dark_gray>");
        }

        Console::log("\n\t----- STDOUT: \n".implode("\n", $j->stdout));
        Console::log("\n\t----- STDERR: \n".implode("\n", $j->stderr));
        Console::log("\n\t----- EXITCODE: {$j->exitcode}");
        Console::log('---------------------------------');

        if ($showCommands) {
            if ($j->exitcode=0) Console::output("<green>OK: {$j->exitcode}</green>");
            else Console::output("<red>FAIL: {$j->exitcode}</red>");
        }

        return  $j->close();
    }

    public function __construct($command) {
        $this->command = "($command) 3>/dev/null; code=$?; echo \$code >&3; exit \$code";
        $this->process = proc_open($this->command, [['pipe', 'r'],['pipe', 'w'],['pipe', 'w'], ['pipe', 'w']], $this->pipes);

        $this->unblockPipes();

    }

    public function close() {
        $this->closePipes();
        proc_close($this->process);

        return $this;
    }

    public function __deconstruct() {
        $this->close();
    }

    public function stdin($input) {
        fwrite($this->pipes[0], $input);
    }

    public function isRunning() {
        return $this->getStatus()->running;
    }

    public function getStatus() {
        $this->status = (object) proc_get_status($this->process);
        return $this->status;
    }

    public function __toString() {
        return print_r($this, true);
    }

    public function failed() {
        return ! in_array($this->exitcode, [0]);
    }



    protected function unblockPipes() {
        foreach ($this->pipes as &$pipe) {
            stream_set_blocking($pipe, 0);
        }
    }

    protected function readStreams($blocking, $close = false)
    {
        $read = [[],[]];

        $r = $this->pipes;
        $w = null;
        $e = null;

        if (! in_array(@stream_select($r, $w, $e, $blocking ? ceil(self::TIMEOUT_PRECISION * 1E6) : 0), [false, 0])) {
            foreach ($r as $pipe) {
                $type = array_search($pipe, $this->pipes);
                $data = preg_replace("/\n/",' ',fread($pipe, 8192));

                if (strlen($data) > 0) {
                    if ($type == 1 ) {
                        $this->stdout[] = $read[0] = $data;
                    }
                    if ($type == 2) {
                        $this->stderr[] = $read[1] = $data;
                    }
                    if ($type == self::EXIT_CODE_STREAM) {
                        $this->exitcode = (int) $data;
                    }
                }
            }
        }

        return $read;
    }

    protected function closePipes()
    {
        foreach ($this->pipes as &$pipe) {
            fclose($pipe);
        }
    }
}