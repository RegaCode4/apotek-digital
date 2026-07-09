<?php

namespace App\Livewire\Inventaris;

use App\Models\Category;
use App\Models\Medicine;
use App\Models\StockMutation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

/** Form tambah/edit obat dalam modal. */
class MedicineForm extends Component
{
    public bool $show = false;

    public ?int $medicineId = null;

    public string $name = '';

    public string $generic_name = '';

    public ?int $category_id = null;

    public string $manufacturer = '';

    public string $unit = 'tablet';

    public string $price = '';

    public int $stock = 0;

    public int $min_stock = 10;

    public ?string $expiry_date = null;

    public bool $requires_prescription = false;

    public string $description = '';

    public ?int $originalStock = null;

    /** Buka modal — isi data jika edit, kosong jika tambah. */
    #[On('open-medicine-form')]
    public function open(?int $medicineId = null): void
    {
        $this->resetValidation();
        $this->medicineId = $medicineId;

        if ($medicineId) {
            $medicine = Medicine::query()->findOrFail($medicineId);

            $this->name = $medicine->name;
            $this->generic_name = $medicine->generic_name ?? '';
            $this->category_id = $medicine->category_id;
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

    /** Tutup modal dan mereset form. */
    public function close(): void
    {
        $this->show = false;
        $this->resetForm();
        $this->resetValidation();
    }

    /** Simpan obat baru atau perbarui yang sudah ada. */
    public function save(): void
    {
        $validated = $this->validate($this->rules());

        DB::transaction(function () use ($validated): void {
            $attributes = [
                'name' => $validated['name'],
                'generic_name' => $validated['generic_name'] ?: null,
                'category_id' => $validated['category_id'] ?: null,
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

                $stockDifference = $validated['stock'] - $previousStock;

                if ($stockDifference > 0) {
                    $this->recordStockMutation($medicine, $stockDifference, 'in', 'Penambahan stok via form edit obat');
                } elseif ($stockDifference < 0) {
                    $this->recordStockMutation($medicine, abs($stockDifference), 'out', 'Pengurangan stok via form edit obat');
                }
            } else {
                $medicine = Medicine::query()->create($attributes);

                if ($validated['stock'] > 0) {
                    $this->recordStockMutation($medicine, $validated['stock'], 'in', 'Stok awal dari form tambah obat');
                }
            }
        });

        $message = $this->medicineId
            ? 'Obat berhasil diperbarui.'
            : 'Obat berhasil ditambahkan.';

        $this->show = false;
        $this->resetForm();
        $this->dispatch('medicine-saved', message: $message);
        $this->dispatch('notify', type: 'success', message: $message);
    }

    /** Menampilkan modal form obat. */
    public function render(): View
    {
        return view('livewire.inventaris.medicine-form', [
            'categoryOptions' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'generic_name' => ['nullable', 'string', 'max:200'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
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

    /** Mereset seluruh field form ke nilai default. */
    protected function resetForm(): void
    {
        $this->medicineId = null;
        $this->originalStock = null;
        $this->name = '';
        $this->generic_name = '';
        $this->category_id = null;
        $this->manufacturer = '';
        $this->unit = 'tablet';
        $this->price = '';
        $this->stock = 0;
        $this->min_stock = 10;
        $this->expiry_date = null;
        $this->requires_prescription = false;
        $this->description = '';
    }

    /** Catat mutasi stok di tabel stock_mutations. */
    protected function recordStockMutation(Medicine $medicine, int $quantity, string $type, string $notes): void
    {
        StockMutation::query()->create([
            'medicine_id' => $medicine->id,
            'type' => $type,
            'quantity' => $quantity,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }
}
