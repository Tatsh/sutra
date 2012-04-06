<?php
require './00-global.php';

class sProcessTest extends PHPUnit_Framework_TestCase {
  const SKIP_MESSAGE = 'Not yet testing for other OS\'s besides Linux, OS X, Solaris, and BSD.';

  private static $root;

  public function setUp() {
    self::$root = getcwd();
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The executable specified, "aaaaa", does not exist or is not in the path.
   */
  public function testConstructorBadExecutable() {
    new sProcess('aaaaa');
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage The executable specified, "aaaaa", does not exist or is not in the path.
   */
  public function testConstructorBadExcecutableInArray() {
    new sProcess(array('aaaaa'));
  }

  public function getObject($name) {
    $args = func_get_args();
    array_shift($args);
    return new sProcess($name, $args);
  }

  public function testConstructorWithArguments() {
    new sProcess('curl', 'a', '"b"', '\'c\'', 'd');
    new sProcess('curl -v -F "aa=1&b=2"');
    new sProcess('curl', 'a');
    new sProcess('curl', 'a', 'b', 'c');
  }

  /**
   * @expectedException fValidationException
   * @expectedExceptionMessage The directory specified, a bad directory, does not exist or is not readable
   */
  public function testSetWorkingDirectoryBadDirectory() {
    $proc = $this->getObject('curl');
    $proc->setWorkingDirectory('a bad directory');
  }

  public function testSetWorkingDirectory() {
    $proc = $this->getObject('curl');
    $proc->setWorkingDirectory('.');
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Working directory "non-writable-directory" is not writable.
   */
  public function testSetWorkingDirectoryNonWritableDirectory() {
    if (fCore::checkOS('windows')) {
      $this->markTestSkipped();
      return;
    }

    $proc = $this->getObject('curl');
    $dir_name = 'non-writable-directory';

    mkdir($dir_name, 0500);
    $proc->setWorkingDirectory($dir_name);
    chdir(self::$root);
  }

  /**
   * @expectedException sProcessException
   */
  public function testTossIfUnexpected() {
    $proc = $this->getObject('curl');
    if (!$proc) {
      $this->markTestSkipped(self::SKIP_MESSAGE);
      return;
    }
    $proc->tossIfUnexpected();
    $proc->redirectStdErr(TRUE, '&1');
    $proc->execute(); // curl returns 2 if no arguments are passed
  }

  public function testRedirectStdErrorToNull() {
    $proc = $this->getObject('curl');
    if (!$proc) {
      $this->markTestSkipped(self::SKIP_MESSAGE);
      return;
    }
    $proc->redirectStdErr();
  }

  public function testSetPath() {
    sProcess::setPath('path1:path2:/usr/share/php');
    sProcess::setPath(); // reset
  }

  public function testExecuteNoExceptionNoArguments() {
    $proc = $this->getObject('curl');
    $proc->redirectStdErr(TRUE, '&1');
    $output = $proc->execute();
    $this->assertStringStartsWith('curl:', $output);
  }

  public function testExecuteNoExceptionWithArguments() {
    $proc = $this->getObject('curl', '--help');
    $output = $proc->execute();
    $this->assertStringStartsWith('Usage: curl [options...] <url>', $output);

    if (!fCore::checkOS('windows')) {
      $proc = $this->getObject('printf', 'abc%d', '1');
      $output = $proc->execute();
      $this->assertStringStartsWith('abc1', $output);
    }
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Attempted to open an interactive session when there is already one active.
   */
  public function testInteractiveAlreadyActive() {
    $program = fCore::checkOS('windows') ? 'ftp' : 'bc';
    $proc = $this->getObject($program);
    $proc->beginInteractive();
    $proc->beginInteractive();
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Attempted to set setting to program already running.
   */
  public function testRedirectStdErrorAlreadyRunning() {
    $program = fCore::checkOS('windows') ? 'ftp' : 'bc';
    $proc = $this->getObject($program);
    $proc->beginInteractive();
    $proc->redirectStandardError();
  }

  public function testInteractive() {
    $is_windows = fCore::checkOS('windows');
    $program = $is_windows ? 'ftp' : 'bc';
    $write = $is_windows ? 'quit' : '1 * 2';
    $proc = $this->getObject($program);
    $proc->beginInteractive();
    $proc->write($write);
    $proc->EOF();
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Attempted to write to non-existent handle.
   */
  public function testInteractiveNonExistantHandle() {
    $is_windows = fCore::checkOS('windows');
    $program = $is_windows ? 'ftp' : 'bc';
    $write = $is_windows ? 'quit' : '1 * 2';
    $proc = $this->getObject($program);
    $proc->write($write);
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Attempted to close non-existent handle.
   */
  public function testCloseNonExistantHandle() {
    $program = fCore::checkOS('windows') ? 'ftp' : 'bc';
    $proc = $this->getObject($program);
    $proc->EOF();
  }

  /**
   * @expectedException sProcessException
   * @expectedExceptionMessage Return value was not expected value: (got: 0, wanted: 2).
   */
  public function testTossIfUnexpectedInteractive() {
    $is_windows = fCore::checkOS('windows');
    $program = $is_windows ? 'ftp' : 'bc';
    $write = $is_windows ? 'quit' : '1 * 2';
    $proc = $this->getObject($program);
    $proc->tossIfUnexpected();
    $proc->beginInteractive();
    $proc->write($write);
    $proc->EOF(2);
  }

  /**
   * @expectedException fProgrammerException
   * @expectedExceptionMessage Attempted to add arguments to a program already running.
   */
  public function testAddArgumentAlreadyRunning() {
    $program = fCore::checkOS('windows') ? 'ftp' : 'bc';
    $proc = $this->getObject($program);
    $proc->tossIfUnexpected();
    $proc->beginInteractive();
    $proc->addArgument('a');
    $proc->EOF(2);
  }

  public function testAddArgument() {
	$program = fCore::checkOS('windows') ? 'compact' : 'bc';
    $proc = $this->getObject($program);
    $proc->addArgument('--help');
  }
}
