Contributor Guidelines for Magallanes
=====================================
Welcome to Magallanes! We are much appreciated you've decided to contribute to this project!
Please read the following guidelines to make your and our work easier and cleaner.

**TL;DR**

1. Write clean code with no mess left
2. Contribute the docs when adding configurable new feature
3. Create your pull request from `nostromo` branch
4. Ensure your code is fully covered by tests

----------

# Reporting Issues
If you have a problem or you've noticed a bug, please feel free to open a new issue. However follow the rules below:
* First, make sure that similar/the same issue doesn't already exists
* If you've already found the solution of the problem you are about to report, please feel free to open a new Pull Request. Then follow the rules below in **Developing Magallanes** section.
* If you are able to, include some test cases or steps to reproduce the bug for us to examine the problem and find a solution.

## Opening Pull Requests
Pull Request is a very powerful tool, so let's be measured in its usage. Always commit code which has at least 95% of coverage, and if it's a new feature always provide concrete tests.
In order to have the PRs prioritized name them with the following tags.

```
[#66] Add new CONTRIBUTING document
[FIX] Set correct permissions on deploy stage
[FEATURE] Create new PermissionsTask
[HOTFIX] Exception not caught on deployment
```
All Pull Requests must be done to the `nostromo` branch, only exception are Hotfixes.
Remember of square brackets when adding issue number. If you'd forget adding them, your whole message will be a comment!

# Developing Magallanes
## Branches
The flow is pretty simple.
In most common cases we work on the `nostromo` branch. It's the branch with the main development for the current major version. All Pull Requests must merge with that branch. The `master` branch is used to move the validated code and generate the releases in an orderly fashion, also we could use it for hotfixes.

If you want to use developing branch in your code, simple pass `dev-nostromo` to dependency version in your `composer.json` file:
```json
{
	"require": {
		"andres-montanez/magallanes": "dev-nostromo"
	}
}
```

## Organization and code quality
We use [PSR2](http://www.php-fig.org/psr/psr-2/) as PHP coding standard.

### Tools you can use to ensure your code quality

1. PHP-CodeSniffer
2. [PHP Mess Detector](https://phpmd.org/)
3. PHP Copy/Paste Detector
4. PHP Dead Code Detector
5. [PHP Coding Standards Fixer](http://cs.sensiolabs.org) with --level=psr2

## Testing and quality
We use PHPUnit to test our code. Most of the project is covered with tests, so if you want your code to be merged push it with proper testing and coverage (at least 95%). To execute the tests with code coverage report:
```
vendor/bin/phpunit --coverage-clover build/logs/coverage.xml
vendor/bin/coveralls -v --coverage_clover build/logs/coverage.xml
```

Tests structure follow almost the same structure as production code with `Test` suffix in class and file name. Follow the tests already made as guidelines.

# Last Words
Thank you for using Magallanes, and special thanks for making it better. When adding features always have in mind the main goal *Deploy code from A to B, and run some tasks*, and think if the feature is aiming at that.

