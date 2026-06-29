<?php

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/** Komponen notifikasi toast berbasis stack */
class ToastNotification extends Component
{
    /** Stack toast aktif */
    public array $toasts = [];

    private int $nextId = 0;

    /** Menambahkan toast ke dalam stack */
    #[On('notify')]
    public function addToast(string $type, string $message): void
    {
        $this->toasts[] = [
            'id' => ++$this->nextId,
            'type' => $type,
            'message' => $message,
        ];
    }

    /** Menghapus toast berdasarkan ID */
    public function dismiss(int $id): void
    {
        $this->toasts = array_values(
            array_filter($this->toasts, fn (array $t): bool => $t['id'] !== $id)
        );
    }

    /** Menampilkan tampilan notifikasi toast */
    public function render(): View
    {
        return view('livewire.components.toast-notification');
    }
}
