<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\MockPaymentGateway;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class MockPaymentGatewayTest extends TestCase
{
    public function test_luhn_validation(): void
    {
        $this->assertTrue(MockPaymentGateway::passesLuhn('4242 4242 4242 4242'));
        $this->assertTrue(MockPaymentGateway::passesLuhn('4000-0000-0000-0002'));
        $this->assertTrue(MockPaymentGateway::passesLuhn('378282246310005'));
        $this->assertFalse(MockPaymentGateway::passesLuhn('4242 4242 4242 4241'));
        $this->assertFalse(MockPaymentGateway::passesLuhn('1234'));
        $this->assertFalse(MockPaymentGateway::passesLuhn(''));
    }

    public function test_expiry_validation(): void
    {
        $now = CarbonImmutable::create(2026, 11, 13);

        $this->assertTrue(MockPaymentGateway::isValidExpiry('11/26', $now));
        $this->assertTrue(MockPaymentGateway::isValidExpiry('01/30', $now));
        $this->assertFalse(MockPaymentGateway::isValidExpiry('10/26', $now));
        $this->assertFalse(MockPaymentGateway::isValidExpiry('13/30', $now));
        $this->assertFalse(MockPaymentGateway::isValidExpiry('1230', $now));
    }

    public function test_brand_detection(): void
    {
        $this->assertSame('Visa', MockPaymentGateway::brand('4242424242424242'));
        $this->assertSame('Mastercard', MockPaymentGateway::brand('5555555555554444'));
        $this->assertSame('American Express', MockPaymentGateway::brand('378282246310005'));
        $this->assertSame('Card', MockPaymentGateway::brand('9999'));
    }

    public function test_decline_cards(): void
    {
        $gateway = new MockPaymentGateway;

        $this->assertNull($gateway->declineReason('4242 4242 4242 4242'));
        $this->assertNotNull($gateway->declineReason('4000 0000 0000 0002'));
        $this->assertNotNull($gateway->declineReason('4000000000009995'));
    }
}
