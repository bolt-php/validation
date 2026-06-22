<?php

namespace framework\validation\tests\Unit;

use framework\validation\attributes\Between;
use framework\validation\attributes\Confirmed;
use framework\validation\attributes\Email;
use framework\validation\attributes\Length;
use framework\validation\attributes\Number;
use framework\validation\attributes\Required;
use framework\validation\Validation;
use PHPUnit\Framework\TestCase;
use stdClass;

class AttributesTest extends TestCase
{
    /**
     * Reset the static registry between tests so they don't bleed into each other.
     */
    protected function setUp(): void
    {
        $reflection = new \ReflectionClass(Validation::class);
        $registry = $reflection->getProperty('registry');
        $registry->setAccessible(true);
        $registry->setValue(null, []);
    }

    public function testBetween() {
        $data = new stdClass();
        $data->good = 5;
        $data->bad = 10;

        $errors = Validation::validate($data, [
            'good' => [new Between(2, 8)],
            'bad' => [new Between(2, 8)],
        ]);

        $this->assertArrayHasKey('bad', $errors);
        $this->assertArrayNotHasKey('good', $errors);
    }
    
    public function testConfirmed() {
        $data = new stdClass();
        $data->good = '1234567';
        $data->good_confirmed = '1234567';
        $data->bad = '1234567';

        $errors = Validation::validate($data, [
            'good' => [new Confirmed('good_confirmed')],
            'bad' => [new Confirmed('bad_confirmed')],
        ]);

        $this->assertArrayHasKey('bad', $errors);
        $this->assertArrayNotHasKey('good', $errors);
    }
    
    public function testEmail() {
        $data = new stdClass();
        $data->good = 'good@example.com';
        $data->bad = 'not-an-email';

        $errors = Validation::validate($data, [
            'good' => [new Email()],
            'bad' => [new Email()],
        ]);

        $this->assertArrayHasKey('bad', $errors);
        $this->assertArrayNotHasKey('good', $errors);
    }
    
    public function testLength() {
        $data = new stdClass();
        $data->good = '12345';
        $data->bad = '12345678';

        $errors = Validation::validate($data, [
            'good' => [new Length(min: 0, max: 7)],
            'bad' => [new Length(min: 0, max: 7)],
        ]);

        $this->assertArrayHasKey('bad', $errors);
        $this->assertArrayNotHasKey('good', $errors);
    }
    
    public function testNumber() {
        $data = new stdClass();
        $data->good = 123;
        $data->bad = 'not a number';

        $errors = Validation::validate($data, [
            'good' => [new Number()],
            'bad' => [new Number()],
        ]);

        $this->assertArrayHasKey('bad', $errors);
        $this->assertArrayNotHasKey('good', $errors);
    }
    
    public function testRequired() {
        $data = new stdClass();
        $data->good = 'available';
        $data->bad = '';

        $errors = Validation::validate($data, [
            'good' => [new Required()],
            'bad' => [new Required()],
        ]);

        $this->assertArrayHasKey('bad', $errors);
        $this->assertArrayNotHasKey('good', $errors);
    }
}