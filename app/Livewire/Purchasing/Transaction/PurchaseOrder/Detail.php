<?php

namespace App\Livewire\Purchasing\Transaction\PurchaseOrder;

use App\Helpers\Core\UserStateHandler;
use App\Helpers\General\Alert;
use App\Helpers\General\FileHelper;
use App\Helpers\General\NumberFormatter;
use App\Helpers\General\ImageLocationHelper;
use App\Models\Finance\Master\Tax;
use App\Models\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProduct;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Finance\Master\Tax\TaxRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductTaxRepository;
use App\Repositories\Purchasing\Transaction\PurchaseOrder\PurchaseOrderProductAttachmentRepository;
use App\Settings\SettingPurchasing;
use App\Settings\SettingCore;

class Detail extends Component
{
    use WithFileUploads;

    public $objId;

    #[Validate('required', message: 'Tanggal Penerimaan Harus Diisi', onUpdate: false)]
    public $transactionDate;
    public $supplierInvoiceNumber;
    public $note;

    public $companyId;
    public $companyText;

    public $supplierId;
    public $supplierText;

    public $warehouseId;
    public $warehouseText;

    public $purchaseOrderProducts = [];
    public $purchaseOrderProductRemoves = [];

    // Helpers
    public $isMultipleCompany = false;
    public $isInputProductCode;
    public $isInputProductExpiredDate;
    public $isInputProductAttachment;
    public $isInputProductBatch;
    public $taxPpnId;
    public $taxPpnName;
    public $taxPpnValue;
    public $companies = [];
    public $warehouses = [];

    public $historyRemarksIds = []; // History Datatable
    public $historyRemarksType = PurchaseOrderProduct::class; // History Datatable

    public function render()
    {
        return view('livewire.purchasing.transaction.purchase-order.detail');
    }

    public function mount()
    {
        $this->loadSetting();
        $this->loadUserState();

        $this->transactionDate = Carbon::now()->format("Y-m-d");
        $this->note = "";
        $this->supplierInvoiceNumber = "";

        if ($this->objId) {
            $purchaseOrder = PurchaseOrderRepository::find(Crypt::decrypt($this->objId));
            $this->transactionDate = Carbon::parse($purchaseOrder->transaction_date)->format("Y-m-d");
            $this->supplierInvoiceNumber = $purchaseOrder->supplier_invoice_number;
            $this->note = $purchaseOrder->note;

            $this->supplierId = Crypt::encrypt($purchaseOrder->supplier_id);
            $this->supplierText = $purchaseOrder->supplier_name;

            $this->warehouseId = Crypt::encrypt($purchaseOrder->warehouse_id);
            $this->warehouseText = $purchaseOrder->warehouse_name;

            $this->companyId = Crypt::encrypt($purchaseOrder->company_id);
            $this->companyText = $purchaseOrder->company_name;

            foreach ($purchaseOrder->purchaseOrderProducts as $purchaseOrderProduct) {
                $unitDetailChoice = UnitDetailRepository::getOptions($purchaseOrderProduct->unit_detail_unit_id);
                $unitDetailId = collect($unitDetailChoice)->filter(function ($obj) use ($purchaseOrderProduct) {
                    return Crypt::decrypt($obj['id']) == $purchaseOrderProduct->unit_detail_id;
                })->first()['id'];

                $uploadedFiles = [];
                foreach ($purchaseOrderProduct->purchaseOrderProductAttachments as $attachment) {
                    $uploadedFiles[] = [
                        'id' => Crypt::encrypt($attachment['id']),
                        'file_name' => $attachment['file_name'],
                        'original_file_name' => $attachment['original_file_name'],
                        'note' => $attachment['note'],
                        'path' => null,
                        'url' => $attachment->getFile(),
                    ];
                }

                $this->purchaseOrderProducts[] = [
                    'id' => Crypt::encrypt($purchaseOrderProduct->id),

                    // Helpers
                    'is_deletable' => $purchaseOrderProduct->isDeletable(),

                    // Core Information
                    'product_id' => Crypt::encrypt($purchaseOrderProduct->product_id),
                    'product_text' => $purchaseOrderProduct->product_name,
                    "unit_detail_id" => $unitDetailId,
                    "unit_detail_choice" => $unitDetailChoice,
                    "quantity" => NumberFormatter::valueToImask($purchaseOrderProduct->quantity),
                    "price" => NumberFormatter::valueToImask($purchaseOrderProduct->price),

                    // Tax Information
                    'tax_id' => $purchaseOrderProduct->ppn ? Crypt::encrypt($purchaseOrderProduct->ppn->tax_id) : null,
                    "is_ppn" => $purchaseOrderProduct->ppn ? true : false,

                    // Additional Information
                    "code" => $purchaseOrderProduct->code,
                    "batch" => $purchaseOrderProduct->batch,
                    "expired_date" => $purchaseOrderProduct->expired_date,

                    // Files
                    'files' => [],
                    'uploadedFiles' => $uploadedFiles,
                    'uploadedFileRemoves' => [],
                ];

                // Set Default PPN
                if ($purchaseOrderProduct->ppn) {
                    $this->taxPpnId = Crypt::encrypt($purchaseOrderProduct->ppn->tax_id);
                    $this->taxPpnName = $purchaseOrderProduct->ppn->tax_name;
                    $this->taxPpnValue = $purchaseOrderProduct->ppn->tax_value;
                }

                // History Datatable
                $this->historyRemarksIds[] = $purchaseOrderProduct->id;
            }
        }
    }

    public function loadSetting()
    {
        $this->isMultipleCompany = SettingCore::get(SettingCore::MULTIPLE_COMPANY);

        $this->isInputProductCode = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_CODE);
        $this->isInputProductExpiredDate = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_EXPIRED_DATE);
        $this->isInputProductAttachment = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_ATTACHMENT);
        $this->isInputProductBatch = SettingPurchasing::get(SettingPurchasing::PURCHASE_ORDER_PRODUCT_BATCH);

        $taxPpn = TaxRepository::find(SettingPurchasing::get(SettingPurchasing::TAX_PPN_ID));
        $this->taxPpnId = Crypt::encrypt($taxPpn->id);
        $this->taxPpnName = $taxPpn->name;
        $this->taxPpnValue = $taxPpn->value;
    }

    public function loadUserState()
    {
        $userState = UserStateHandler::get();
        if ($this->isMultipleCompany) {
            $this->companies = $userState['companies'];
            $this->companyId = $userState['company_id'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        } else {
            $this->companyId = $userState['company_id'];
            $this->warehouses = $userState['warehouses'];
            $this->warehouseId = $userState['warehouse_id'];
        }
    }

    public function updated($property, $value)
    {
        if (str_contains($property, 'purchaseOrderProducts')) {
            if (str_contains($property, 'files') && $value) {
                $this->addFile($property);
            }
        }
    }

    public function store()
    {
        if (!$this->supplierId) {
            Alert::fail($this, "Gagal", "Supplier Belum Diinput");
            return;
        }
        if (!$this->companyId) {
            Alert::fail($this, "Gagal", "Perusahaan Belum Diinput");
            return;
        }
        if (!$this->warehouseId) {
            Alert::fail($this, "Gagal", "Gudang Belum Diinput");
            return;
        }
        if (count($this->purchaseOrderProducts) == 0) {
            Alert::fail($this, "Gagal", "Data Pembelian Produk Belum Diinput");
            return;
        }

        $this->validate();

        $validatedData = [
            'supplier_id' => Crypt::decrypt($this->supplierId),
            'company_id' => Crypt::decrypt($this->companyId),
            'warehouse_id' => Crypt::decrypt($this->warehouseId),
            'transaction_date' => $this->transactionDate,
            'note' => $this->note,
            'supplier_invoice_number' => $this->supplierInvoiceNumber,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $decId = Crypt::decrypt($this->objId);
                PurchaseOrderRepository::update($decId, $validatedData);
                $purchaseOrder = PurchaseOrderRepository::find($decId);
            } else {
                $purchaseOrder = PurchaseOrderRepository::create($validatedData);
            }

            $objId = $purchaseOrder->id;

            // ===============================
            // ===== GOOD RECEIVE PRODUCT ====
            // ===============================
            foreach ($this->purchaseOrderProductRemoves as $removedId) {
                PurchaseOrderProductRepository::delete(Crypt::decrypt($removedId));
            }

            foreach ($this->purchaseOrderProducts as $item) {
                $validatedData = [
                    'purchase_order_id' => $objId,
                    'product_id' => Crypt::decrypt($item['product_id']),
                    'unit_detail_id' => Crypt::decrypt($item['unit_detail_id']),
                    'quantity' => NumberFormatter::imaskToValue($item['quantity']),
                    'price' => NumberFormatter::imaskToValue($item['price']),
                    'code' => $item['code'],
                    'batch' => $item['batch'],
                    'expired_date' => $item['expired_date']
                ];

                if ($item['id']) {
                    $purchaseOrderProductId = Crypt::decrypt($item['id']);
                    $object = PurchaseOrderProductRepository::update($purchaseOrderProductId, $validatedData);
                } else {
                    $object = PurchaseOrderProductRepository::create($validatedData);
                    $purchaseOrderProductId = $object->id;
                }

                // ====================================
                // == GOOD RECEIVE ORDER PRODUCT TAX ==
                // ====================================
                $tax = PurchaseOrderProductTaxRepository::findBy(whereClause: [['purchase_order_product_id', $purchaseOrderProductId], ['tax_type', Tax::TYPE_PPN]]);
                if ($tax) {
                    if (!$item['is_ppn']) {
                        PurchaseOrderProductTaxRepository::delete($tax->id);
                    }
                } else {
                    if ($item['is_ppn']) {
                        PurchaseOrderProductTaxRepository::create([
                            'purchase_order_product_id' => $purchaseOrderProductId,
                            'tax_id' => $item['tax_id'] ? Crypt::decrypt($item['tax_id']) : Crypt::decrypt($this->taxPpnId),
                        ]);
                    }
                }

                // =====================================
                // == GOOD RECEIVE PRODUCT ATTACHMENT ==
                // =====================================
                foreach ($item['uploadedFileRemoves'] as $item) {
                    $object = PurchaseOrderProductAttachmentRepository::delete(Crypt::decrypt($item));
                }

                foreach ($item['uploadedFiles'] as $file) {
                    if ($file['id']) {
                        PurchaseOrderProductAttachmentRepository::update(Crypt::decrypt($file['id']), [
                            'note' => $file['note'],
                        ]);
                    } else {
                        $newPath = FileHelper::LOCATION_PRODUCT_DETAIL_ATTACHMENT . basename($file['path']);
                        Storage::move($file['path'], $newPath);
                        $file['path'] = $newPath;

                        PurchaseOrderProductAttachmentRepository::create([
                            'purchase_order_product_id' => $purchaseOrderProductId,
                            'note' => $file['note'],
                            'file_name' => $file['file_name'],
                            'original_file_name' => $file['original_file_name'],
                        ]);
                    }
                }
            }

            if ($this->objId) {
                $purchaseOrder->onUpdated();
            } else {
                $purchaseOrder->onCreated();
            }

            DB::commit();

            Alert::confirmation(
                $this,
                Alert::ICON_SUCCESS,
                "Berhasil",
                "Data Berhasil Diperbarui",
                "on-dialog-confirm",
                "on-dialog-cancel",
                "Oke",
                "Tutup",
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('purchase_order.edit', $this->objId);
        } else {
            $this->redirectRoute('purchase_order.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('purchase_order.index');
    }

    /*
    | HANDLER : PURCHASE ORDER ATTACHMENT
    */
    public function addFile($property)
    {
        $properties = explode('.', $property);
        $index = $properties[1];
        $this->validate([
            'purchaseOrderProducts.files.*' => 'file|max:1024',
        ]);
        foreach ($this->purchaseOrderProducts[$index]['files'] as $file) {
            $file_path = $file->store('tmp_attachments', 'public');
            $this->purchaseOrderProducts[$index]['uploadedFiles'][] = [
                'id' => null,
                'file_name' => $file->hashName(),
                'note' => "",
                'original_file_name' => $file->getClientOriginalName(),
                'path' => $file_path,
                'url' => Storage::url($file_path)
            ];
        }
    }

    public function removeFile($index, $fileIndex)
    {
        if ($this->purchaseOrderProducts[$index]['uploadedFiles'][$fileIndex]['id']) {
            $this->purchaseOrderProducts[$index]['uploadedFileRemoves'][] = $this->purchaseOrderProducts[$index]['uploadedFiles'][$fileIndex]['id'];
        }
        unset($this->purchaseOrderProducts[$index]['uploadedFiles'][$fileIndex]);
    }

    /*
    | HANDLER : PURCHASE ORDER PRODUCT
    */
    public function addDetail($productId)
    {
        $product = ProductRepository::find(Crypt::decrypt($productId));
        $unit_detail_choice = UnitDetailRepository::getOptions($product->unit_id);

        $this->purchaseOrderProducts[] = [
            'id' => null,

            // Helpers
            'is_deletable' => true,

            // Core Information
            'product_id' => Crypt::encrypt($product->id),
            'product_text' => $product->name,
            "unit_detail_id" => $unit_detail_choice[0]['id'],
            "unit_detail_choice" => $unit_detail_choice,
            "quantity" => 0,
            "price" => 0,

            // Tax Information
            'tax_id' => null,
            "is_ppn" => false,

            // Additional Information
            "code" => null,
            "batch" => null,
            "expired_date" => null,

            // Files
            'files' => [],
            'uploadedFiles' => [],
            'uploadedFileRemoves' => [],
        ];
    }

    public function removeDetail($index)
    {
        if ($this->purchaseOrderProducts[$index]['id']) {
            $this->purchaseOrderProductRemoves[] = $this->purchaseOrderProducts[$index]['id'];
        }
        unset($this->purchaseOrderProducts[$index]);
        $this->purchaseOrderProducts = array_values($this->purchaseOrderProducts);
    }

    public function duplicateDetail($index)
    {
        $copy = $this->purchaseOrderProducts[$index];

        $item = [
            'id' => null,

            // Helpers
            'is_deletable' => true,

            // Core Information
            'product_id' => $copy['product_id'],
            'product_text' => $copy['product_text'],
            "unit_detail_id" => $copy['unit_detail_id'],
            "unit_detail_choice" => $copy['unit_detail_choice'],
            "quantity" => $copy['quantity'],
            "price" => $copy['price'],

            // Tax Information
            'tax_id' => $copy['tax_id'],
            "is_ppn" => $copy['is_ppn'],

            // Additional Information
            "code" => $copy['code'],
            "batch" => $copy['batch'],
            "expired_date" => $copy['expired_date'],

            // Files
            "files" => $copy['files'],
            "uploadedFiles" => $copy['uploadedFiles'],
            "uploadedFileRemoves" => $copy['uploadedFileRemoves'],
        ];

        array_splice($this->purchaseOrderProducts, $index + 1, 0, [$item]);
    }
}
