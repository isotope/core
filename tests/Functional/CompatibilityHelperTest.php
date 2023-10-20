<?php

declare(strict_types=1);

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 *
 * @link       https://isotopeecommerce.org
 *
 *
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 *
 *
 */

namespace Isotope\Tests;

use Contao\System;
use Contao\TestCase\FunctionalTestCase;
use Isotope\CompatibilityHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Test the CompatibilityHelper class.
 *
 * @covers \Isotope\CompatibilityHelper
 */
class CompatibilityHelperTest extends FunctionalTestCase
{
    /**
     * Test the getCurrentRequest() method.
     *
     * As this method is private, we test it implicitely through the other tests.
     * Or we temporarily change its scope to public and run this testcase.
     */
    // public function testGetCurrentRequest()
    // {
    //     $requestStack = new RequestStack();
    //     $request = new Request();
    //     $request->server->set('REQUEST_URI', '/example');
    //     $requestStack->push($request);

    //     $this->getContainer()->set('request_stack', $requestStack);
    //     System::setContainer($this->getContainer());

    //     $currentRequest = CompatibilityHelper::getCurrentRequest();

    //     $this->assertInstanceOf(Request::class, $currentRequest);
    // }

    /**
     * Test the isBackend() method.
     */
    public function testIsBackend(): void
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $request->server->set('REQUEST_URI', '/contao');
        $request->attributes->set('_scope', 'backend');
        $requestStack->push($request);

        $this->getContainer()->set('request_stack', $requestStack);
        System::setContainer($this->getContainer());

        $isBackend = CompatibilityHelper::isBackend();

        $this->assertTrue($isBackend);
    }

    /**
     * Test the isFrontend() method.
     */
    public function testIsFrontend(): void
    {
        $requestStack = new RequestStack();
        $request = new Request();
        $request->server->set('REQUEST_URI', '/');
        $request->attributes->set('_scope', 'frontend');

        $requestStack->push($request);

        $this->getContainer()->set('request_stack', $requestStack);
        System::setContainer($this->getContainer());

        $isFrontend = CompatibilityHelper::isFrontend();

        $this->assertTrue($isFrontend);
    }
}
