<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment; 
use Illuminate\Queue\SerializesModels;

class RespaldoVentasMail extends Mailable
{
    use Queueable, SerializesModels;

    public $rutaArchivo;

    public function __construct($rutaArchivo)
    {
        $this->rutaArchivo = $rutaArchivo;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Respaldo Mensual de Ventas - Panadería',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.respaldo', // Asegúrate de tener esta vista creada también
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->rutaArchivo)
                ->as('ventas_archivadas.csv')
                ->withMime('text/csv'),
        ];
    }
}