<?php

declare(strict_types=1);

namespace Tests\Unit\Campaigns\Domain;

use PHPUnit\Framework\TestCase;
use Src\Campaigns\Domain\Exceptions\CampaignAlreadyCancelledException;
use Src\Campaigns\Domain\Exceptions\CampaignException;
use Src\Campaigns\Domain\Exceptions\CampaignInvalidStatusTransitionException;
use Src\Campaigns\Domain\Exceptions\CampaignMemberAlreadyEnrolledException;
use Src\Campaigns\Domain\Exceptions\CampaignMemberNotFoundException;
use Src\Campaigns\Domain\Exceptions\CampaignNameAlreadyExistsException;
use Src\Campaigns\Domain\Exceptions\CampaignNotFoundException;

class CampaignExceptionsTest extends TestCase
{
    public function test_not_found_exception_carries_uuid_in_message(): void
    {
        $uuid = 'abc-123';

        $exception = CampaignNotFoundException::withUuid($uuid);

        $this->assertInstanceOf(CampaignNotFoundException::class, $exception);
        $this->assertStringContainsString($uuid, $exception->getMessage());
        $this->assertSame("Campaign not found with UUID: {$uuid}", $exception->getMessage());
    }

    public function test_name_exists_exception_carries_name_in_message(): void
    {
        $name = 'Navidad 2026';

        $exception = CampaignNameAlreadyExistsException::withName($name);

        $this->assertInstanceOf(CampaignNameAlreadyExistsException::class, $exception);
        $this->assertStringContainsString($name, $exception->getMessage());
        $this->assertSame("A campaign with this name already exists: {$name}", $exception->getMessage());
    }

    public function test_already_cancelled_exception_carries_uuid_in_message(): void
    {
        $uuid = 'xyz-789';

        $exception = CampaignAlreadyCancelledException::withUuid($uuid);

        $this->assertInstanceOf(CampaignAlreadyCancelledException::class, $exception);
        $this->assertStringContainsString($uuid, $exception->getMessage());
        $this->assertSame("Campaign is already cancelled: {$uuid}", $exception->getMessage());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidTransitionProvider')]
    public function test_invalid_transition_exception_carries_both_statuses(string $from, string $to): void
    {
        $exception = CampaignInvalidStatusTransitionException::from($from, $to);

        $this->assertInstanceOf(CampaignInvalidStatusTransitionException::class, $exception);
        $this->assertStringContainsString($from, $exception->getMessage());
        $this->assertStringContainsString($to, $exception->getMessage());
        $this->assertSame("Cannot transition campaign from '{$from}' to '{$to}'.", $exception->getMessage());
    }

    public static function invalidTransitionProvider(): array
    {
        return [
            'draft to completed'    => ['draft', 'completed'],
            'active to draft'       => ['active', 'draft'],
            'completed to active'   => ['completed', 'active'],
            'completed to draft'    => ['completed', 'draft'],
            'completed to cancelled'=> ['completed', 'cancelled'],
        ];
    }

    public function test_member_not_found_exception_carries_uuid_in_message(): void
    {
        $uuid = 'member-uuid-123';

        $exception = CampaignMemberNotFoundException::withUuid($uuid);

        $this->assertInstanceOf(CampaignMemberNotFoundException::class, $exception);
        $this->assertStringContainsString($uuid, $exception->getMessage());
    }

    public function test_member_already_enrolled_exception_carries_oids_in_message(): void
    {
        $exception = CampaignMemberAlreadyEnrolledException::forMember(42, 7);

        $this->assertInstanceOf(CampaignMemberAlreadyEnrolledException::class, $exception);
        $this->assertStringContainsString('42', $exception->getMessage());
        $this->assertStringContainsString('7', $exception->getMessage());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('campaignExceptionClassesProvider')]
    public function test_campaign_exceptions_extend_campaign_exception(string $class): void
    {
        $this->assertTrue(is_a($class, CampaignException::class, true));
    }

    public static function campaignExceptionClassesProvider(): array
    {
        return [
            'CampaignNotFoundException'               => [CampaignNotFoundException::class],
            'CampaignNameAlreadyExistsException'      => [CampaignNameAlreadyExistsException::class],
            'CampaignAlreadyCancelledException'       => [CampaignAlreadyCancelledException::class],
            'CampaignInvalidStatusTransitionException'=> [CampaignInvalidStatusTransitionException::class],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('memberExceptionClassesProvider')]
    public function test_member_exceptions_extend_campaign_member_exception(string $class): void
    {
        $this->assertTrue(is_a($class, \Src\Campaigns\Domain\Exceptions\CampaignMemberException::class, true));
    }

    public static function memberExceptionClassesProvider(): array
    {
        return [
            'CampaignMemberNotFoundException'        => [CampaignMemberNotFoundException::class],
            'CampaignMemberAlreadyEnrolledException' => [CampaignMemberAlreadyEnrolledException::class],
        ];
    }

    public function test_campaign_exception_is_throwable(): void
    {
        $this->expectException(CampaignException::class);

        throw CampaignNotFoundException::withUuid('any-uuid');
    }
}
