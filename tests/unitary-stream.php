<?php

declare(strict_types=1);

use MaplePHP\Http\Stream;
use MaplePHP\Unitary\{Expect, TestCase};


group('MaplePHP\Http\Stream', function (TestCase $case) {

    // -------------------------------------------------
    // Simple value expectations
    // -------------------------------------------------

    $case->expect((new Stream())->getStream())
        ->isString()
        ->isEqualTo('php://temp')
        ->validate();

    $case->expect(is_resource((new Stream())->getResource()))
        ->isTrue()
        ->validate();


    // -------------------------------------------------
    // write / read behaviour
    // -------------------------------------------------
    $case->check(function (Expect $expect) {

        $s = new Stream(Stream::TEMP, 'w+');
        $bytes = $s->write('Hello');

        $expect->against($bytes)
            ->isInt()
            ->isGreaterThan( 1);
    });


    $case->check(function (Expect $expect) {

        $s = new Stream(Stream::TEMP, 'w+');
        $s->write('HelloWorld');
        $s->rewind();

        $first = $s->read(5);
        $rest  = $s->getContents();

        $expect->against($first)->isEqualTo('Hello');
        $expect->against($rest)->isEqualTo('World');
    });


    // -------------------------------------------------
    // __toString rewind behaviour
    // -------------------------------------------------

    $case->check(function (Expect $expect) {

        $s = new Stream(Stream::TEMP, 'w+');
        $s->write('ABC');
        $s->read(1);

        $expect->against((string)$s)
            ->isString()
            ->isEqualTo('ABC');
    });


    // -------------------------------------------------
    // seek / tell
    // -------------------------------------------------

    $case->check(function (Expect $expect) {

        $s = new Stream(Stream::TEMP, 'w+');
        $s->write('ABCDE');
        $s->rewind();
        $s->read(2);

        $expect->against($s->tell())
            ->isInt()
            ->isEqualTo(2);
    });


    // -------------------------------------------------
    // getLines
    // -------------------------------------------------

    $case->check(function (Expect $expect) {

        $s = new Stream(Stream::TEMP, 'w+');
        $s->write("A\nB\nC\nD\n");

        $expect->against($s->getLines(2, 3))
            ->isString()
            ->isEqualTo("B\nC\n");
    });


    // -------------------------------------------------
    // clean
    // -------------------------------------------------

    $case->check(function (Expect $expect) {

        $s = new Stream(Stream::TEMP, 'w+');
        $s->write('12345');
        $s->clean();

        $expect->against($s->getSize())
            ->isInt()
            ->isEqualTo(0);
    });


    // -------------------------------------------------
    // close / detach
    // -------------------------------------------------

    $case->check(function (Expect $expect) {

        $s = new Stream(Stream::TEMP, 'w+');
        $s->close();

        $expect->against($s->getResource())
            ->isNull();
    });

});