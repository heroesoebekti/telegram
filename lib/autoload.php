<?php

$namespaces = [
    "Lib\\" => "/",
];

foreach ($namespaces as $namespace => $classpaths) {
    if (!is_array($classpaths)) {
        $classpaths = array($classpaths);
    }
    spl_autoload_register(function ($classname) use ($namespace, $classpaths) {
        if (preg_match("#^" . preg_quote($namespace) . "#", $classname)) {
            $classname = str_replace($namespace, "", $classname);
            $filename = preg_replace("#\\\\#", "/", $classname) . ".php";
            foreach ($classpaths as $classpath) {
                $fullpath = __DIR__ . "/" . $classpath . "/$filename";
                if (file_exists($fullpath)) include_once $fullpath;
            }
        }
    });
}
