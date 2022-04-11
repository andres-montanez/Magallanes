CHANGELOG for 5.X
=================

* 5.0.0 (2022-04-15)
  * v5 series release.
  * Refactored for Symfony 6 and PHP 8.
  * Added strong types.
  * Removed task `composer/self-update`.
  * Allow `exec` task to interpolate `%environment%` and `%release%`.
  * Added new `sleep` task to day execution [PR#414].
  * Added new `symlink` option to define the name of symbolic link on the Release [PR#425].
  * Improved Windows compatibility [PR#427].
  * Added new `log_limit` option to limit how many logs are kept [Issue#403].
  * Add new deploy option `--tag` to specify deploying a specific tag [Issue#192] [Issue#315].
  * Added new `scp_flags` option for the `scp` command when SSH flags are incompatible with [Issue#439].
