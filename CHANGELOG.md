CHANGELOG for 3.X
=================

* 3.1.0 (2017-02-25)
 * Add new Exec task to execute arbitrary shell commands
 * Add new Composer task, to update phar (composer/self-update)
 * [#344] Allow to flag Filesystem tasks
 * [PR#346] Add new File System task, to change file's modes (fs/chmod)
 * [BUGFIX] [PR#342] Ignore empty exclude lines
 * [PR#330] Allow Composer task options to be overwritten at environment level
 * [PR#330] Add new method Runtime::getMergedOption to merge ConfigOption and EnvOption
 * [Documentation] [PR#333] Improve example config file

* 3.0.1 (2017-01-10)
 * [BUGFIX] [#350] [#353] Fix escape issue when commands are sent through SSH

* 3.0.0 (2017-01-31)
 * v3 series release
