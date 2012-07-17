<?php
function buildPath() {
  $cmps = func_get_args();

  return implode(DIRECTORY_SEPARATOR, $cmps);
}

// Register an autoloader for PHPDoctor classes.
$phpDocBasePath = realpath(__DIR__ . '/../..');
$phpDocClassPath = buildPath($phpDocBasePath, 'classes');

spl_autoload_register(function ($className) use ($phpDocClassPath) {

  $className = lcfirst($className);

  $fullPath = $phpDocClassPath . "/$className.php";

  if (file_exists($fullPath)) {
    require $fullPath;
  }
});

set_include_path(get_include_path() . PATH_SEPARATOR . $phpDocBasePath);

// Constant that points to the default configuration file
$phpDocDefaultConfigPath = buildPath($phpDocBasePath, 'default.ini');
define('PHPDOCTOR_DEFAULT_CONFIG', $phpDocDefaultConfigPath);

// Function that parses the source found at the given directory relative to the
// unit test directory
function parseSource($dir) {
  $phpdoc = new \PhpDoctor(PHPDOCTOR_DEFAULT_CONFIG);
  $phpdoc->setOption('source_path', "./$dir");
  $phpdoc->setOption('quiet', true);

  $rootDoc = $phpdoc->parse();
  return $rootDoc;
}
