<?php

namespace Tests\Unit;

use App\Jobs\ProcessEmailsJob;
use App\Mail\CustomEmail;
use App\Models\PaymentSlip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ProcessEmailsJobTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_send_emails_and_update_payment_slip_status()
    {
        Mail::fake();
        Log::shouldReceive('info')->times(3);
        Log::shouldReceive('warning')->never();
        Log::shouldReceive('error')->never();

        $paymentSlip1 = PaymentSlip::create(['name' => 'User1', 'governmentId' => 1111, 'debtAmount' => 100, 'debtDueDate' => '2024-06-25', 'email' => 'user1@example.com', 'debtId' => 'debt1', 'status' => 'pending']);
        $paymentSlip2 = PaymentSlip::create(['name' => 'User2', 'governmentId' => 1112, 'debtAmount' => 101, 'debtDueDate' => '2024-06-25', 'email' => 'user2@example.com', 'debtId' => 'debt2', 'status' => 'pending']);

        $chunk = [
            ['debtId' => 'debt1', 'email' => 'user1@example.com', 'name' => 'User 1'],
            ['debtId' => 'debt2', 'email' => 'user2@example.com', 'name' => 'User 2'],
        ];

        $job = new ProcessEmailsJob($chunk);
        $job->handle();

        Mail::assertSent(CustomEmail::class, 2);

        $this->assertEquals('sent', $paymentSlip1->fresh()->status);
        $this->assertEquals('sent', $paymentSlip2->fresh()->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_should_update_payment_slip_status_to_error_on_mail_failure()
    {
        Mail::fake();
        Mail::shouldReceive('to->send')->andThrow(new \Exception('Mail error'));

        Log::shouldReceive('info')->once(); 
        Log::shouldReceive('warning')->never();
        Log::shouldReceive('error')->once();

        $paymentSlip = PaymentSlip::create(['name' => 'User1', 'governmentId' => 1111, 'debtAmount' => 100, 'debtDueDate' => '2024-06-25', 'email' => 'user1@example.com', 'debtId' => 'debt3', 'status' => 'pending']);

        $chunk = [
            ['debtId' => 'debt3', 'email' => 'user3@example.com', 'name' => 'User 3'],
        ];

        $job = new ProcessEmailsJob($chunk);
        $job->handle();
        $this->assertEquals('error', $paymentSlip->fresh()->status);
    }
}
