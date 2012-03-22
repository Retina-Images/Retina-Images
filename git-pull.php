<?php

$user   = 'Retina-Images';
$repo   = 'Retina-Images';
$branch = 'master';

$commands = array();
$commands[] = 'rm -R *';
$commands[] = "curl -L --insecure https://github.com/{$user}/{$repo}/tarball/{$branch} -o {$branch}.tar.gz";
$commands[] = "tar -xzf {$branch}.tar.gz";
$commands[] = "rm $branch.tar.gz";
$commands[] = "mv {$user}-{$repo}*/* .";
$commands[] = "mv {$user}-{$repo}*/.[^.]* .";
$commands[] = "rm {$user}-{$repo}*";

$command = implode(';', $commands);
shell_exec($command);
