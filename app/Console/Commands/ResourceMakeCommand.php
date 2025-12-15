<?php

namespace App\Console\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use ToneflixCode\ResourceModifier\Commands\ResourceMakeCommand as ToneflixCodeResourceMakeCommand;

#[AsCommand(name: 'make:resource')]
class ResourceMakeCommand extends ToneflixCodeResourceMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:resource';
}
