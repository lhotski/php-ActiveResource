<?php

use ActiveResource\Ext\Inflector;

class InflectorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider pluralizeDataProvider
     */
    public function testPluralize($source, $result)
    {
        $this->assertEquals($result, Inflector::pluralize($source));
    }

    /**
     * @dataProvider pluralizeDataProvider
     */
    public function testSingularize($result, $source)
    {
        $this->assertEquals($result, Inflector::singularize($source));
    }

    public function pluralizeDataProvider()
    {
        return array(
            array('status', 'statuses'),
            array('quiz', 'quizzes'),
            array('buffalo', 'buffaloes'),
            array('man', 'men'),
            array('axis', 'axes'),
            array('testis', 'testes'),
            array('crisis', 'crises'),
            array('dwarf', 'dwarves'),
            array('mouse', 'mice'),
            array('ox', 'oxen'),
            array('atlas', 'atlases'),
            array('beef', 'beefs'),
            array('corpus', 'corpuses'),
            array('cow', 'cows'),
            array('ganglion', 'ganglions'),
            array('genie', 'genies'),
            array('genus', 'genera'),
            array('graffito', 'graffiti'),
            array('hoof', 'hoofs'),
            array('loaf', 'loaves'),
            array('money', 'monies'),
            array('mongoose', 'mongooses'),
            array('move', 'moves'),
            array('niche', 'niches'),
            array('numen', 'numina'),
            array('occiput', 'occiputs'),
            array('penis', 'penises'),
            array('turf', 'turfs'),
        );
    }

}
