<?php

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->setUsingCache(true)
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in(__DIR__)
	)
;

// vim:ft=php
