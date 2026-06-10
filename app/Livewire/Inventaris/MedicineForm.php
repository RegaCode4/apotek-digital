<?php

namespace App\Livewire\Inventaris;

use App\Models\Medicine;
use App\Models\StockMutation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class MedicineForm extends Component
{
    public bool $show = false;

    public ?int $medicineId = null;

    public string $name = '';

    public string $generic_name = '';

    public string $category = '';

    public string $manufacturer = '';

    public string $unit = 'tablet';

    public string $price = '';

    public int $stock = 0;

    public int $min_stock = 10;

    public ?string $expiry_date = null;

    public bool $requires_prescription = false;

    public string $description = '';

    public ?int $originalStock = null;

    #[On('open-medicine-form')]
    public function open(?int $medicineId = null): void
    {
        $this->resetValidation();
        $this->medicineId = $medicineId;

        if ($medicineId) {
            $medicine = Medicine::query()->findOrFail($medicineId);

            $this->name = $medicine->name;
            $this->generic_name = $medicine->generic_name ?? '';
            $this->category = $medicine->category ?? '';
            $this->manufacturer = $medicine->manufacturer ?? '';
            $this->unit = $medicine->unit;
            $this->price = (string) $medicine->price;
            $this->stock = $medicine->stock;
            $this->min_stock = $medicine->min_stock;
            $this->expiry_date = $medicine->expiry_date?->format('Y-m-d');
            $this->requires_prescription = $medicine->requires_prescription;
            $this->description = $medicine->description ?? '';
            $this->originalStock = $medicine->stock;
        } else {
            $this->resetForm();
        }

        $this->show = true;
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules());

        DB::transaction(function () use ($validated): void {
            $attributes = [
                'name' => $validated['name'],
                'generic_name' => $validated['generic_name'] ?: null,
                'category' => $validated['category'] ?: null,
                'manufacturer' => $validated['manufacturer'] ?: null,
                'unit' => $validated['unit'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'min_stock' => $validated['min_stock'],
                'expiry_date' => $validated['expiry_date'] ?: null,
                'requires_prescription' => $validated['requires_prescription'],
                'description' => $validated['description'] ?: null,
            ];

            if ($this->medicineId) {
                $medicine = Medicine::query()->findOrFail($this->medicineId);
                $previousStock = $this->originalStock ?? $medicine->stock;

                $medicine->update($attributes);

                $stockIncrease = $validated['stock'] - $previousStock;

                if ($stockIncrease > 0) {
                    $this->recordStockMutation($medicine, $stockIncrease, 'Penambahan stok via form edit obat');
                }
            } else {
                $medicine = Medicine::query()->create($attributes);

                if ($validated['stock'] > 0) {
                    $this->recordStockMutation($medicine, $validated['stock'], 'Stok awal dari form tambah obat');
                }
            }
        });

        $message = $this->medicineId
            ? 'Obat berhasil diperbarui.'
            : 'Obat berhasil ditambahkan.';

        session()->flash('success', $message);

        $this->show = false;
        $this->resetForm();
        $this->dispatch('medicine-saved', message: $message);
    }

    public function render(): View
    {
        return view('livewire.inventaris.medicine-form');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'generic_name' => ['nullable', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:50'],
            'manufacturer' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'requires_prescription' => ['boolean'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function resetForm(): void
    {
        $this->medicineId = null;
        $this->originalStock = null;
        $this->name = '';
        $this->generic_name = '';
        $this->category = '';
        $this->manufacturer = '';
        $this->unit = 'tablet';
        $this->price = '';
        $this->stock = 0;
        $this->min_stock = 10;
        $this->expiry_date = null;
        $this->requires_prescription = false;
        $this->description = '';
    }

    protected function recordStockMutation(Medicine $medicine, int $quantity, string $notes): void
    {
        StockMutation::query()->create([
            'medicine_id' => $medicine->id,
            'type' => 'in',
            'quantity' => $quantity,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }
}
