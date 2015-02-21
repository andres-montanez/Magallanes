Contributor guidelines for Magallanes
=====================================
Welcome to Magallanes! We are much appreciated you've decided to contribute to this project!
Please read the following guidelines to make your and our work easier and cleaner.

**TL;DR**

1. Write clean code with no mess left
2. Contribute the docs when adding configurable new feature
3. Create your pull request from `develop` branch
4. Ensure your code is fully covered by tests


----------

# Reporting issues
If you have a problem or you've noticed some bug, please feel free to open new issue. However please follow the rules below:
* First, make sure that similar/the same issue doesn't already exist
* If you've already found the solution of the problem you are about to report, please feel free to open a new pull request. Then follow the rules below in **Developing Magallanes** section.
* If you are able to, include some test cases or steps to reproduce the bug for us to examine the problem to reach the problem origin.

## Opening pull requests
Pull Request are actually some kind of issues with code, so please follow the rules above in **Reporting issues** section before making the pull requests.
Our code isn't so beautiful, tested and testable as we would like it to be but if you're pushing your code please be sure it meets the requirements from **Organization and code quality** chapter. We want to improve the existing code to facilitate extending it and making fixes quicker. So if you are editing some class and you find it ugly, please do not ignore it. Following [The Boy Scout Rule](http://www.informit.com/articles/article.aspx?p=1235624&seqNum=6) - *Leave the campground cleaner than you found it* - we all can improve the existing code.
Keep your git commits as atomic as possible. It brings better history description only by commit messages and allow us to eventually revert the single commits with no affects. Your commit messages should be also descriptive. The first line of commit should be short, try to limit it up to 50 characters. The messages should be written imperatively, like following:
```
Add MyCustomTask
```
If you need to write more about your tasks, please enter the description in the next lines. There you can write whatever you want, like why you made this commit and what it consists of.
```
Add MyCustomTask

This task has very important role for the project. I found this very useful for all developers. I think the deploy with it should be a lot easier.
```

Optionally you can tag your messages in square brackets. It can be issue number or simple flag. Examples:
```
[#183] Add new CONTRIBUTING document
[FIX] Set correct permissions on deploy stage
[FEATURE] Create new PermissionsTask
```
Remember of square brackets when adding issue number. If you'd forget adding them, your whole message will be a comment!

## Contributing the documentation
Magallanes is made to deploy your application quick and with no need to write redudant code. Usage is as simple as writing the configuration for target project in YAML files. In the nearest future we would like to make some Wiki with all available options, tasks and commands. For now, the only "documentation" are example files in `docs` directory. If the code you are going to include in your pull requests adds or changes config options, please make sure that you create a new sample in those files. You should also do the same with commands.

# Developing Magallanes
## Branches
The flow is pretty simple.
In most common cases we work on `develop` branch. It's a branch with the newest changes which sometimes need more testing. All pull requests are opened to be merged into that branch. That keeps us safe to not deploy unsafe code into production - `master` branch. When we decide that every changeset in `develop` is tested manually and works as it's intented, we merge it to master.
If the change you commited is pretty hot and needs to be released ASAP, you are allowed to make a pull request to `master` branch. But it's the only case, please try to avoid it. All pull request that are not made on `develop` will be rejected.
If you want to use develop branch in your code, simple pass `dev-develop` to dependency version in your `composer.json` file:
```json
{
	"require": {
		"andres-montanez/magallanes": "dev-develop"
	}
}
```
## Organization and code quality
We use [PSR2](http://www.php-fig.org/psr/psr-2/) as PHP coding standard.
Some of the rules we follow that are not included in document above:

* Variables' and properties' names are camelCased (e.g.: `$thisIsMyVariable`)
* Avoid too long or too short variables' and methods' names, like `$thisIsMyAwesomeVariableAndImProudOfIt`
* Names of your properties/methods should be intuitive and self-describing - that means your code should look like a book. Developers who read the code should immediately know what a variable includes or what a method does.
* Let your methods will be verbs. For boolean methods, prefix it with `is`, `has`, and so on. E.g.: `isConfigurable`, `hasChildren`.
* Be [SOLID](http://en.wikipedia.org/wiki/SOLID_%28object-oriented_design%29) and follow [KISS](http://en.wikipedia.org/wiki/KISS_principle) - let the class be responsible only for its tasks.
* Write testable code and if there's a need - easy extendible.
* Avoid duplications

The rules above have been set a long time after the project has started. If you notice some violations, please open a new issue or even pull request with fixes. It'll be much appreciated.

### Tools you can use to ensure your code quality

1. **PHP-CodeSniffer**
2. **PHP Mess Detector**
3. PHP Copy/Paste Detector
4. PHP Dead Code Detector
5. [PHP Coding Standards Fixer](http://cs.sensiolabs.org)

## Testing and quality
We use PHPUnit to test our code. The whole project is not covered with tests right now but we've been working on it for some time. If you want your code to be merged into Magallanes, we want you to push it with proper tests. We would love to reach and keep at least 90% of line code coverage. In short time we want to configure quality tools to make sure your code is tested properly with minimum coverage. Anyway, try to keep 100% of Code Coverage in your pull requests. To run your tests with code coverage report, you can either run it with:
```
bin/phpunit --coverage-text
```
or with more friendly and detailed user graphical representation, into HTML:
```
bin/phpunit --coverate-html report
```
where `report` is the directory where html report files shall be stored.
Tests structure follow the same structure as production code with `Test` suffix in class and file name. All tests should go to `tests` directory in project root.  So if you've created a class `Mage\Tasks\BuilIn\NewTask` the testing class should be called `MageTest\Tasks\BuiltIn\NewTaskTest`. 
To provide more strict tests, point what the method actually test and omit testing some classes indirectly, remember to add annotations to your tests:

* **`@coversDefaultClass` class annotations**
This prevent to to write full class name each time you write `@covers` for test method (see next point)
```php

/**
 * @coversDefaultClass Mage\Console\Colors
 */
class ColorsTest extends PHPUnit_Framework_TestCase
{
```
* **`@covers` methods annotations**
```php
/**
 * @covers ::add
 */
public function testAddOnePlusOne()
{
	// ...
}
```
**Note:** If you omit `coversDefaultClass` for test class, you need to write full class name in `@covers` annotation.

**Test class musn't test more than one class and any other classes than class being actually tested**

## Configuration
Magallanes configuration is kept in YAML files. Please follow those rules while adding or changing the configuration:
* Keep 2 spaces indentation in each level
* Multi-word config keys should be joined with dash (`-`), like `my-custom-task`
* If your contribution includes new config key, please be sure that you've documented it in configuration documentation.
