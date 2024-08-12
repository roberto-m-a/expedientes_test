<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Notifications\notificacionRegistroCorreo;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\AnonymousNotifiable;

class notificacionRegistroCorreoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_an_email_notification()
    {
        // Simula el envío de notificaciones
        Notification::fake();

        // Crea un usuario simulado
        $user = new class {
            public $id = 1;
            public $email = 'user@example.com';

            public function getKey()
            {
                return $this->id;
            }

            public function getEmailForVerification()
            {
                return $this->email;
            }
        };

        // Enviar la notificación
        Notification::send($user, new notificacionRegistroCorreo());

        // Verificar que la notificación se envió
        Notification::assertSentTo(
            [$user], notificacionRegistroCorreo::class
        );
    }

    /** @test */
    public function email_notification_contains_correct_content()
    {
        // Simula el envío de notificaciones
        Notification::fake();

        // Crea un usuario simulado
        $user = new class {
            public $id = 1;
            public $email = 'user@example.com';

            public function getKey()
            {
                return $this->id;
            }

            public function getEmailForVerification()
            {
                return $this->email;
            }
        };

        // Enviar la notificación
        Notification::send($user, new notificacionRegistroCorreo());

        // Obtener la notificación enviada
        $notification = Notification::sent($user, notificacionRegistroCorreo::class)->first();

        // Verificar el contenido del correo
        $this->assertEquals('Expedientes ITO - Verifica tu correo electrónico', $notification->toMail($user)->subject);
        $this->assertStringContainsString('Se te ha creado una cuenta en la plataforma de Expedientes ITO', $notification->toMail($user)->render());
        $this->assertStringContainsString('Haz clic en el botón de abajo para verificar tu dirección de correo electrónico.', $notification->toMail($user)->render());
        $this->assertStringContainsString('Si no creaste una cuenta, no se requiere ninguna otra acción.', $notification->toMail($user)->render());
    }
}
