<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerify;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;


class MiNotificacionDeVerificacionDeEmail extends BaseVerify // Podrías extender Illuminate\Auth\Notifications\VerifyEmail
{
  /**
   * Construye la URL firmada de verificación.
   */
  protected function verificationUrl($notifiable)
  {
    return URL::temporarySignedRoute(
      'verification.verify',            // nombre de ruta que definiremos
      Carbon::now()->addMinutes(60),    // expiración
      [
        'id'   => $notifiable->getKey(),
        'hash' => sha1($notifiable->getEmailForVerification()),
      ]
    );
  }

  /**
   * Personaliza el correo.
   */
  public function toMail($notifiable): MailMessage
  {
    $url = $this->verificationUrl($notifiable);

    return (new MailMessage)
      ->subject('Verifica tu correo en GeoEncuestas')
      ->greeting("¡Hola {$notifiable->nombre_completo}!")
      ->line('Por favor, haz clic en el botón para verificar tu dirección de correo electrónico:')
      ->action('Verificar mi correo', $url)
      ->line('Si no creaste una cuenta, puedes ignorar este email.')
      ->salutation('Saludos, el equipo de GeoEncuestas');
  }
}
