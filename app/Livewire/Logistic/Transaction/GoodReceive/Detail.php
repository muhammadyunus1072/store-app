<?php

namespace App\Livewire\Logistic\Transaction\GoodReceive;

use Exception;
use App\Helpers\Alert;
use App\Helpers\Core\UserStateHelper;
use App\Helpers\NumberFormatter;
use App\Helpers\ImageLocationHelper;
use App\Models\Core\Setting\Setting;
use App\Models\Finance\Master\Tax;
use App\Models\Logistic\Master\Product\Product;
use App\Repositories\Core\Company\CompanyRepository;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Core\Setting\SettingRepository;
use App\Repositories\Finance\Master\Tax\TaxRepository;
use App\Repositories\Logistic\Master\Product\ProductRepository;
use App\Repositories\Logistic\Master\Unit\UnitDetailRepository;
use App\Repositories\Logistic\Master\Warehouse\WarehouseRepository;
use App\Repositories\Logistic\Transaction\GoodReceive\GoodReceiveRepository;
use App\Repositories\Logistic\Transaction\GoodReceive\GoodReceiveProductRepository;
use App\Repositories\Logistic\Transaction\GoodReceive\GoodReceiveProductTaxRepository;
use App\Repositories\Logistic\Transaction\GoodReceive\GoodReceiveProductAttachmentRepository;
use Carbon\Carbon;

class Detail extends Component
{
    use WithFileUploads;

    public $objId;

    #[Validate('required', message: 'Tanggal Penerimaan Harus Diisi', onUpdate: false)]
    public $receive_date;
    public $supplier_invoice_number;
    public $note;

    public $company_id;
    public $company_text;

    public $supplier_id;
    public $supplier_text;

    public $warehouse_id;
    public $warehouse_text;

    public $goodReceiveProducts = [];
    public $goodReceiveProductRemoves = [];

    // Helpers
    public $default_ppn_id;
    public $default_ppn_name;
    public $default_ppn_value;

    public $setting_product_code;
    public $setting_product_expired_date;
    public $setting_product_attachment;
    public $setting_product_batch;

    public function render()
    {
        return view('livewire.logistic.transaction.good-receive.detail');
    }

    public function mount()
    {
        $this->receive_date = Carbon::now()->format("Y-m-d");

        $userState = UserStateHelper::get();
        if ($userState['company_id']) {
            $company = CompanyRepository::find($userState['company_id']);
            $this->company_id = Crypt::encrypt($company->id);
            $this->company_text = $company->name;
        }
        if ($userState['warehouse_id']) {
            $warehouse = WarehouseRepository::find($userState['warehouse_id']);
            $this->warehouse_id = Crypt::encrypt($warehouse->id);
            $this->warehouse_text = $warehouse->name;
        }

        $setting = SettingRepository::findByName(Setting::NAME_LOGISTIC);
        $settings = json_decode($setting->setting);
        $this->setting_product_code = $settings->product_code;
        $this->setting_product_expired_date = $settings->product_expired_date;
        $this->setting_product_attachment = $settings->product_attachment;
        $this->setting_product_batch = $settings->product_batch;

        $ppn = TaxRepository::find($settings->tax_ppn_good_receive_id);
        $this->default_ppn_id = Crypt::encrypt($ppn->id);
        $this->default_ppn_name = $ppn->name;
        $this->default_ppn_value = $ppn->value;

        if ($this->objId) {
            $goodReceive = GoodReceiveRepository::findWithDetails(Crypt::decrypt($this->objId));

            $this->supplier_id = Crypt::encrypt($goodReceive->supplier_id);
            $this->supplier_text = $goodReceive->supplier_name;

            $this->warehouse_id = Crypt::encrypt($goodReceive->warehouse_id);
            $this->warehouse_text = $goodReceive->warehouse_name;

            $this->company_id = Crypt::encrypt($goodReceive->company_id);
            $this->company_text = $goodReceive->company_name;

            $this->receive_date = Carbon::parse($goodReceive->receive_date)->format("Y-m-d");
            $this->supplier_invoice_number = $goodReceive->supplier_invoice_number;
            $this->note = $goodReceive->note;

            foreach ($goodReceive->goodReceiveProducts as $goodReceiveProduct) {
                $unit_detail_choice = UnitDetailRepository::getOptions($goodReceiveProduct->unit_detail_unit_id);
                $unit_detail_id = collect($unit_detail_choice)->filter(function ($obj) use ($goodReceiveProduct) {
                    return Crypt::decrypt($obj['id']) == $goodReceiveProduct->unit_detail_id;
                })->first()['id'];

                $uploadedFiles = [];
                foreach ($goodReceiveProduct->goodReceiveProductAttachments as $value) {
                    $uploadedFiles[] = [
                        'id' => Crypt::encrypt($value['id']),
                        'file_name' => $value['file_name'],
                        'original_file_name' => $value['original_file_name'],
                        'note' => $value['note'],
                        'path' => null,
                        'url' => $value->getFile(),
                    ];
                }

                $this->goodReceiveProducts[] = [
                    'id' => Crypt::encrypt($goodReceiveProduct->id),

                    // Core Information
                    'product_id' => Crypt::encrypt($goodReceiveProduct->product_id),
                    'product_text' => $goodReceiveProduct->product_name,
                    "unit_detail_id" => $unit_detail_id,
                    "unit_detail_choice" => $unit_detail_choice,
                    "quantity" => NumberFormatter::valueToImask($goodReceiveProduct->quantity),
                    "price" => NumberFormatter::valueToImask($goodReceiveProduct->price),

                    // Tax Information
                    'tax_id' => $goodReceiveProduct->ppn ? Crypt::encrypt($goodReceiveProduct->ppn->tax_id) : null,
                    "is_ppn" => $goodReceiveProduct['ppn'] ? true : false,

                    // Additional Information
                    "code" => $goodReceiveProduct->code,
                    "batch" => $goodReceiveProduct->batch,
                    "expired_date" => $goodReceiveProduct->expired_date,

                    // Files
                    'files' => [],
                    'uploadedFiles' => $uploadedFiles,
                    'uploadedFileRemoves' => [],
                ];
            }
        }
    }

    public function updated($property, $value)
    {
        if (str_contains($property, 'goodReceiveProducts')) {
            if (str_contains($property, 'files') && $value) {
                $this->addFile($property);
            }
        }
    }

    public function store()
    {
        if (!$this->supplier_id) {
            Alert::fail($this, "Gagal", "Supplier Belum Diinput");
            return;
        }
        if (!$this->company_id) {
            Alert::fail($this, "Gagal", "Perusahaan Belum Diinput");
            return;
        }
        if (!$this->warehouse_id) {
            Alert::fail($this, "Gagal", "Gudang Belum Diinput");
            return;
        }
        if (count($this->goodReceiveProducts) == 0) {
            Alert::fail($this, "Gagal", "Data Pembelian Produk Belum Diinput");
            return;
        }

        $this->validate();

        $validatedData = [
            'supplier_id' => Crypt::decrypt($this->supplier_id),
            'company_id' => Crypt::decrypt($this->company_id),
            'warehouse_id' => Crypt::decrypt($this->warehouse_id),
            'receive_date' => $this->receive_date,
            'note' => $this->note,
            'supplier_invoice_number' => $this->supplier_invoice_number,
        ];

        try {
            DB::beginTransaction();
            if ($this->objId) {
                $decId = Crypt::decrypt($this->objId);
                GoodReceiveRepository::update($decId, $validatedData);
                $goodReceive = GoodReceiveRepository::find($decId);
            } else {
                $goodReceive = GoodReceiveRepository::create($validatedData);
            }

            $objId = $goodReceive->id;

            // ===============================
            // ===== GOOD RECEIVE PRODUCT ====
            // ===============================
            foreach ($this->goodReceiveProductRemoves as $removedId) {
                GoodReceiveProductRepository::delete(Crypt::decrypt($removedId));
            }

            foreach ($this->goodReceiveProducts as $item) {
                $validatedData = [
                    'good_receive_id' => $objId,
                    'product_id' => Crypt::decrypt($item['product_id']),
                    'unit_detail_id' => Crypt::decrypt($item['unit_detail_id']),
                    'quantity' => NumberFormatter::imaskToValue($item['quantity']),
                    'price' => NumberFormatter::imaskToValue($item['price']),
                    'code' => $item['code'],
                    'batch' => $item['batch'],
                    'expired_date' => $item['expired_date']
                ];

                if ($item['id']) {
                    $goodReceiveProductId = Crypt::decrypt($item['id']);
                    $object = GoodReceiveProductRepository::update($goodReceiveProductId, $validatedData);
                } else {
                    $object = GoodReceiveProductRepository::create($validatedData);
                    $goodReceiveProductId = $object->id;
                }

                // ====================================
                // == GOOD RECEIVE ORDER PRODUCT TAX ==
                // ====================================
                $tax = GoodReceiveProductTaxRepository::findBy(whereClause: [['good_receive_product_id', $goodReceiveProductId], ['tax_type', Tax::TYPE_PPN]]);
                if ($tax) {
                    if (!$item['is_ppn']) {
                        GoodReceiveProductTaxRepository::delete($tax->id);
                    }
                } else {
                    if ($item['is_ppn']) {
                        GoodReceiveProductTaxRepository::create([
                            'good_receive_product_id' => $goodReceiveProductId,
                            'tax_id' => $item['tax_id'] ? Crypt::decrypt($item['tax_id']) : Crypt::decrypt($this->default_ppn_id),
                        ]);
                    }
                }

                // =====================================
                // == GOOD RECEIVE PRODUCT ATTACHMENT ==
                // =====================================
                foreach ($item['uploadedFileRemoves'] as $item) {
                    $object = GoodReceiveProductAttachmentRepository::delete(Crypt::decrypt($item));
                }

                foreach ($item['uploadedFiles'] as $file) {
                    if ($file['id']) {
                        GoodReceiveProductAttachmentRepository::update(Crypt::decrypt($file['id']), [
                            'note' => $file['note'],
                        ]);
                    } else {
                        $newPath = ImageLocationHelper::FILE_GOOD_RECEIVE_PRODUCT_LOCATION . basename($file['path']);
                        Storage::move($file['path'], $newPath);
                        $file['path'] = $newPath;

                        GoodReceiveProductAttachmentRepository::create([
                            'good_receive_product_id' => $goodReceiveProductId,
                            'note' => $file['note'],
                            'file_name' => $file['file_name'],
                            'original_file_name' => $file['original_file_name'],
                        ]);
                    }
                }
            }

            if ($this->objId) {
                $goodReceive->onUpdated();
            } else {
                $goodReceive->onCreated();
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
        } catch (Exception $e) {
            DB::rollBack();
            Alert::fail($this, "Gagal", $e->getMessage());
        }
    }

    #[On('on-dialog-confirm')]
    public function onDialogConfirm()
    {
        if ($this->objId) {
            $this->redirectRoute('good_receive.edit', $this->objId);
        } else {
            $this->redirectRoute('good_receive.create');
        }
    }

    #[On('on-dialog-cancel')]
    public function onDialogCancel()
    {
        $this->redirectRoute('good_receive.index');
    }

    /*
    | HANDLER : GOOD RECEIVE ATTACHMENT
    */
    public function addFile($property)
    {
        $properties = explode('.', $property);
        $index = $properties[1];
        $this->validate([
            'goodReceiveProducts.files.*' => 'file|max:1024',
        ]);
        foreach ($this->goodReceiveProducts[$index]['files'] as $file) {
            $file_path = $file->store('tmp_attachments', 'public');
            $this->goodReceiveProducts[$index]['uploadedFiles'][] = [
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
        if ($this->goodReceiveProducts[$index]['uploadedFiles'][$fileIndex]['id']) {
            $this->goodReceiveProducts[$index]['uploadedFileRemoves'][] = $this->goodReceiveProducts[$index]['uploadedFiles'][$fileIndex]['id'];
        }
        unset($this->goodReceiveProducts[$index]['uploadedFiles'][$fileIndex]);
    }

    /*
    | HANDLER : GOOD RECEIVE PRODUCT
    */
    public function addDetail($productId)
    {
        $product = ProductRepository::find(Crypt::decrypt($productId));
        $unit_detail_choice = UnitDetailRepository::getOptions($product->unit_id);

        $this->goodReceiveProducts[] = [
            'id' => null,

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
        if ($this->goodReceiveProducts[$index]['id']) {
            $this->goodReceiveProductRemoves[] = $this->goodReceiveProducts[$index]['id'];
        }
        unset($this->goodReceiveProducts[$index]);
        $this->goodReceiveProducts = array_values($this->goodReceiveProducts);
    }

    public function duplicateDetail($index)
    {
        $copy = $this->goodReceiveProducts[$index];

        $item = [
            'id' => null,

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

        array_splice($this->goodReceiveProducts, $index + 1, 0, [$item]);
    }
}
