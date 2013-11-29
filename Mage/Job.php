<?php

namespace Mage;

class Job {
    protected $process;
    protected $pipes = [];
    public $stdout = [];
    public $stderr = [];
    public $status;

    public static function run($command, $showErorrs=true, $showStdout=false)
    {
        $j = new Job($command);
        while ($j->isRunning()) {
            if ($showStdout) echo $j->stdoutLine();
            if ($showErorrs) echo $j->stderrLine();
        }

        return  $j;
    }

    public function __construct($command) {
        Console::log('---------------------------------');
        Console::log('---- Executing: $ ' . $command);

        $this->process = proc_open($command, [0=>['pipe', 'r'], 1=>['pipe', 'w'], 2=>['pipe', 'w']], $this->pipes);

        foreach ($this->pipes as &$pipe) {
            stream_set_blocking($pipe, 0);
        }
    }

    public function close() {
        $this->stdout = $this->getFullPipeContent(1);
        $this->stderr = $this->getFullPipeContent(2);
        foreach ($this->pipes as $i=>&$pipe) {
            fclose($pipe);
        }
        Console::log("\n\t----- STDOUT: \n".$this->stdout);
        Console::log("\n\t----- STDERR: \n".$this->stderr);
        Console::log('---------------------------------');
        return proc_close($this->process);
    }

    public function __deconstruct() {
        $this->close();
    }

    public function stdin($input) {
        fwrite($this->pipes[0], $input);
    }

    public function stdoutLine() {
        $this->stdout[] = $this->readPipeLine(2);
        return end($this->stdout);
    }

    public function stderrLine() {
        $this->stderr[] = $this->readPipeLine(2);
        return end($this->stderr);
    }

    public function getFullPipeContent($num) {
        $pipe = &$this->pipes[$num];
        rewind($pipe);
        return stream_get_contents($pipe);
    }

    protected function readPipeLine($num) {
        $pipe = &$this->pipes[$num];
        return fgets($pipe);
    }

    public function isRunning() {
        return $this->getStatus()->running;
    }

    public function getStatus() {
        return (object) proc_get_status($this->process);
    }
}