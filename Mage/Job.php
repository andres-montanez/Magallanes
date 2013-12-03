<?php

namespace Mage;

class Job {
    protected $process;
    protected $pipes = [];
    public $stdout = [];
    public $stderr = [];
    public $status;
    public $command;
    public $exitcode;

    public static function run($command, $showErorrs=true, $verbose=false)
    {
        $j = new Job($command);
        if ($verbose) Console::output("\n<yellow>".$command."</yellow>\n");

        while ($j->isRunning()) {
            $o = $j->stdoutLine();
            $e = $j->stderrLine();

            if ($showErorrs && !empty($e)) Console::output("<red>$e</red>");
            if ($verbose && !empty($o)) Console::output("<dark_gray>$o</dark_gray>");
        }

        return  $j->close();
    }

    protected function __construct($command) {
        $this->command = $command;
        Console::log('---------------------------------');
        Console::log('---- Executing: $ ' . $this->command);

        $this->process = proc_open($this->command, [0=>['pipe', 'r'], 1=>['pipe', 'w'], 2=>['pipe', 'w']], $this->pipes, null, null, ['bypass_shell'=>true]);

        foreach ($this->pipes as &$pipe) {
            stream_set_blocking($pipe, 0);
        }
    }

    protected function close() {
        foreach ($this->pipes as &$pipe) {
            fclose($pipe);
        }
        $this->status = $this->getStatus();
        $this->exitcode = proc_close($this->process);
        Console::log("\n\t----- STDOUT: \n".implode("\n", $this->stdout));
        Console::log("\n\t----- STDERR: \n".implode("\n", $this->stderr));
        Console::log('---------------------------------');
//        Console::output("<red>{$this->exitcode}</red>");
        return $this;
    }

    public function __deconstruct() {
        $this->close();
    }

    public function stdin($input) {
        fwrite($this->pipes[0], $input);
    }

    public function stdoutLine() {
        if ($line = $this->readPipeLine(1)) {
            $this->stdout[] = $line;
            return end($this->stdout);
        }
    }

    public function stderrLine() {
        if ($line = $this->readPipeLine(2)) {
            $this->stderr[] = $line;
            return end($this->stderr);
        }
    }

    protected function readPipeLine($num) {
        $pipe = &$this->pipes[$num];
        return preg_replace("/\n/",'',fgets($pipe));
    }

    public function isRunning() {

        return $this->getStatus()->running;
    }

    public function getStatus() {
        return (object) proc_get_status($this->process);
    }

    public function __toString() {
        return print_r($this, true);
    }

    public function failed() {
        return ! empty($this->stderr);
        return ! (in_array($this->status->exitcode, [-1,0]) && in_array($this->exitcode, [-1,0]));
    }
}