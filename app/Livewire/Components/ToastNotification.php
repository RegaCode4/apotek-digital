<?php

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ToastNotification extends Component
{
    /**
     * Stack of active toasts.
     *
     * @var array<int, array{id: int, type: string, message: string}>
     */
    public array $toasts = [];

    private int $nextId = 0;

    /**
     * Add a toast to the stack.
     *
     * Dispatched via: $this->dispatch('notify', type: 'success', message: '...')
     *
     * @param  'success'|'error'|'warning'|'info'  $type
     */
    #[On('notify')]
    public function addToast(string $type, string $message): void
    {
        $this->toasts[] = [
            'id' => ++$this->nextId,
            'type' => $type,
            'message' => $message,
        ];
    }

    /**
     * Remove a toast by its ID (called by Alpine after auto-dismiss).
     */
    public function dismiss(int $id): void
    {
        $this->toasts = array_values(
            array_filter($this->toasts, fn (array $t): bool => $t['id'] !== $id)
        );
    }

    public function render(): View
    {
        return view('livewire.components.toast-notification');
    }
}
