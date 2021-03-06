<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the BSD-style license found in the
 *  LICENSE file in the root directory of this source tree. An additional grant
 *  of patent rights can be found in the PATENTS file in the same directory.
 *
 */

namespace Facebook\AutoloadMap;

final class RootImporterTest extends BaseTestCase {
  public function testSelf(): void {
    $root = realpath(__DIR__.'/../');
    $importer = new RootImporter($root);
    $map = $importer->getAutoloadMap();
    $this->assertContains(
      'facebook\autoloadmap\exception',
      array_keys($map['class']),
    );

    $this->assertContains(
      'phpunit_framework_testcase',
      array_keys($map['class']),
    );
    $this->assertEmpty($importer->getFiles());
  }

  public function testImportTree(): void {
    $root = __DIR__.'/fixtures/hh-only';
    $builder = new RootImporter($root);
    $tempfile = tempnam(sys_get_temp_dir(), 'hh_autoload');
    (new Writer())
      ->setBuilder($builder)
      ->setRoot($root)
      ->writeToFile($tempfile);

    $cmd = (Vector {
      PHP_BINARY,
      '-v', 'Eval.Jit=0',
      __DIR__.'/fixtures/hh-only/test.php',
      $tempfile,
    })->map($x ==> escapeshellarg($x));
    $cmd = implode(' ', $cmd);

    $output = [];
    $exit_code = null;
    $result = exec($cmd, $output, $exit_code);

    unlink($tempfile);

    $this->assertSame(0, $exit_code, implode("\n", $output));
    $this->assertSame($result, 'OK!');
  }
}
