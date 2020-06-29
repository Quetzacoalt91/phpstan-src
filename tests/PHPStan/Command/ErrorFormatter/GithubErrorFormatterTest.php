<?php declare(strict_types = 1);

namespace PHPStan\Command\ErrorFormatter;

use PHPStan\File\FuzzyRelativePathHelper;
use PHPStan\Testing\ErrorFormatterTestCase;

class GithubErrorFormatterTest extends ErrorFormatterTestCase
{

	public function dataFormatterOutputProvider(): iterable
	{
		yield [
			'No errors',
			0,
			0,
			0,
			'
 [OK] No errors

',
		];

		yield [
			'One file error',
			1,
			1,
			0,
			' ------ -----------------------------------------------------------------
  Line   folder with unicode 😃/file name with "spaces" and unicode 😃.php
 ------ -----------------------------------------------------------------
  4      Foo
 ------ -----------------------------------------------------------------

 [ERROR] Found 1 error

::error file=folder with unicode 😃/file name with "spaces" and unicode 😃.php,line=4,col=0::Foo
',
		];

		yield [
			'One generic error',
			1,
			0,
			1,
			' -- ---------------------
     Error
 -- ---------------------
     first generic error
 -- ---------------------

 [ERROR] Found 1 error

',
		];

		yield [
			'Multiple file errors',
			1,
			4,
			0,
			' ------ -----------------------------------------------------------------
  Line   folder with unicode 😃/file name with "spaces" and unicode 😃.php
 ------ -----------------------------------------------------------------
  2      Bar
  4      Foo
 ------ -----------------------------------------------------------------

 ------ ---------
  Line   foo.php
 ------ ---------
  1      Foo
  5      Bar
 ------ ---------

 [ERROR] Found 4 errors

::error file=folder with unicode 😃/file name with "spaces" and unicode 😃.php,line=2,col=0::Bar
::error file=folder with unicode 😃/file name with "spaces" and unicode 😃.php,line=4,col=0::Foo
::error file=foo.php,line=1,col=0::Foo
::error file=foo.php,line=5,col=0::Bar
',
		];

		yield [
			'Multiple generic errors',
			1,
			0,
			2,
			' -- ----------------------
     Error
 -- ----------------------
     first generic error
     second generic error
 -- ----------------------

 [ERROR] Found 2 errors

',
		];

		yield [
			'Multiple file, multiple generic errors',
			1,
			4,
			2,
			' ------ -----------------------------------------------------------------
  Line   folder with unicode 😃/file name with "spaces" and unicode 😃.php
 ------ -----------------------------------------------------------------
  2      Bar
  4      Foo
 ------ -----------------------------------------------------------------

 ------ ---------
  Line   foo.php
 ------ ---------
  1      Foo
  5      Bar
 ------ ---------

 -- ----------------------
     Error
 -- ----------------------
     first generic error
     second generic error
 -- ----------------------

 [ERROR] Found 6 errors

::error file=folder with unicode 😃/file name with "spaces" and unicode 😃.php,line=2,col=0::Bar
::error file=folder with unicode 😃/file name with "spaces" and unicode 😃.php,line=4,col=0::Foo
::error file=foo.php,line=1,col=0::Foo
::error file=foo.php,line=5,col=0::Bar
',
		];
	}

	/**
	 * @dataProvider dataFormatterOutputProvider
	 *
	 * @param string $message
	 * @param int    $exitCode
	 * @param int    $numFileErrors
	 * @param int    $numGenericErrors
	 * @param string $expected
	 */
	public function testFormatErrors(
		string $message,
		int $exitCode,
		int $numFileErrors,
		int $numGenericErrors,
		string $expected
	): void
	{
		$relativePathHelper = new FuzzyRelativePathHelper(self::DIRECTORY_PATH, [], '/');
		$formatter = new GithubErrorFormatter(
			$relativePathHelper,
			new TableErrorFormatter($relativePathHelper, false)
		);

		$this->assertSame($exitCode, $formatter->formatErrors(
			$this->getAnalysisResult($numFileErrors, $numGenericErrors),
			$this->getOutput()
		), sprintf('%s: response code do not match', $message));

		$this->assertEquals($expected, $this->getOutputContent(), sprintf('%s: output do not match', $message));
	}

}
