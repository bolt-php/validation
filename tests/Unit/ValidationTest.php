<?php

namespace framework\validation\tests\Unit;

use framework\validation\Validation;
use framework\validation\Validator;
use framework\validation\attributes\Email;
use PHPUnit\Framework\TestCase;
use stdClass;

class ValidationTest extends TestCase
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

    public function testReturnsEmptyArrayWhenAllRulesPass(): void
    {
        $data = new stdClass();
        $data->email = 'user@example.com';

        $errors = Validation::validate($data, [
            'email' => [new Email()],
        ]);

        $this->assertSame([], $errors);
    }

    public function testReturnsErrorMessageWhenValidatorFails(): void
    {
        $data = new stdClass();
        $data->email = 'not-an-email';

        $errors = Validation::validate($data, [
            'email' => [new Email()],
        ]);

        $this->assertArrayHasKey('email', $errors);
        $this->assertSame('Please enter a valid email', $errors['email']);
    }

    public function testStopsOnFirstFailurePerField(): void
    {
        $data = new stdClass();
        $data->email = 'bad';

        $failing = new class extends Validator {
            public $message = 'second-failure';
            public function validate($value, $data = null): bool
            {
                return false;
            }
        };

        $errors = Validation::validate($data, [
            'email' => [new Email(), $failing],
        ]);

        // Email fails first, second validator should not run.
        $this->assertSame('Please enter a valid email', $errors['email']);
    }

    public function testReportsErrorForMissingField(): void
    {
        $data = new stdClass();

        $errors = Validation::validate($data, [
            'missing' => [new Email()],
        ]);

        $this->assertArrayHasKey('missing', $errors);
    }

    public function testAcceptsPipeDelimitedStringOfValidatorNames(): void
    {
        Validation::addValidator('email', new Email());

        $data = new stdClass();
        $data->a = 'good@example.com';
        $data->b = 'bad';

        $errors = Validation::validate($data, [
            'a' => 'email',
            'b' => 'email',
        ]);

        $this->assertArrayNotHasKey('a', $errors);
        $this->assertArrayHasKey('b', $errors);
        $this->assertSame('Please enter a valid email', $errors['b']);
    }

    public function testResolvesValidatorInstancesFromRegistry(): void
    {
        $custom = new class extends Validator {
            public $message = 'custom-failed';
            public function validate($value, $data = null): bool
            {
                return $value === 'expected';
            }
        };

        Validation::addValidator('matches', $custom);

        $data = new stdClass();
        $data->field = 'something-else';

        $errors = Validation::validate($data, [
            'field' => ['matches'],
        ]);

        $this->assertArrayHasKey('field', $errors);
        $this->assertSame('custom-failed', $errors['field']);
    }

    public function testResolvesClosureValidators(): void
    {
        $data = new stdClass();
        $data->name = 'John';

        $errors = Validation::validate($data, [
            'name' => [
                function ($value) {
                    return $value != 'Jane' ? 'expected jane' : null;
                },
            ],
        ]);

        $this->assertArrayHasKey('name', $errors);
        $this->assertSame('expected jane', $errors['name']);
    }

    public function testClosurePassingReturnsNoError(): void
    {
        $data = new stdClass();
        $data->name = 'Jane';

        $errors = Validation::validate($data, [
            'name' => [
                fn ($value) => $value === 'Jane' ? null : 'fail',
            ],
        ]);

        $this->assertArrayNotHasKey('name', $errors);
    }

    public function testInstantiatesClassNameStringsAsValidator(): void
    {
        $data = new stdClass();
        $data->email = 'nope';

        $errors = Validation::validate($data, [
            'email' => [Email::class],
        ]);

        $this->assertArrayHasKey('email', $errors);
        $this->assertSame('Please enter a valid email', $errors['email']);
    }

    public function testProcessesMultipleFields(): void
    {
        $data = new stdClass();
        $data->a = 'good@example.com';
        $data->b = 'bad';

        $errors = Validation::validate($data, [
            'a' => [new Email()],
            'b' => [new Email()],
        ]);

        $this->assertArrayNotHasKey('a', $errors);
        $this->assertArrayHasKey('b', $errors);
    }

    public function testMixedValidatorTypesOnSingleField(): void
    {
        Validation::addValidator('email', new Email());

        $data = new stdClass();
        $data->email = 'not-an-email';

        $errors = Validation::validate($data, [
            'email' => ['email', Email::class, new Email()],
        ]);

        $this->assertArrayHasKey('email', $errors);
    }

    public function testUnknownStringValidatorIsIgnored(): void
    {
        $data = new stdClass();
        $data->email = 'good@example.com';

        $this->expectException(\Error::class);

        Validation::validate($data, [
            'email' => ['this-validator-is-not-registered', new Email()],
        ]);

    }

    public function testCustomErrorMessage(): void
    {
        $data = new stdClass();
        $data->email = 'not-an-email';

        $errors = Validation::validate($data, [
            'email' => [new Email('not an email')]
        ]);

        $this->assertEquals('not an email', $errors['email']);
    }
}