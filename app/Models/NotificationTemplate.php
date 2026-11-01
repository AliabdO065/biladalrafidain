<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $guarded = ['id'];

    protected $attributes = ['language' => 'ar', 'auto_send' => false];

    protected $casts = ['auto_send' => 'boolean'];

    public const FIELDS = [
        'supplier_payment' => ['name', 'reference', 'date', 'supplier', 'amount', 'currency'],
        'receipt' => ['name', 'reference', 'date', 'amount', 'currency', 'category'],
        'shipment' => ['name', 'reference', 'date', 'parcels', 'weight'],
    ];

    public static function defaults(): array
    {
        return [
            'supplier_payment' => "شركة بلاد الرافدين للشحن والخدمات اللوجستية\nالسلام عليكم ورحمة الله وبركاته، عزيزي التاجر {name}،\nتم سداد مبلغ للمصنع نيابةً عنكم.\nرقم السند: {reference}\nالتاريخ: {date}\nالمصنع / المورد: {supplier}\nالمبلغ: {amount} {currency}\nشكرًا لثقتكم بشركة بلاد الرافدين.",
            'receipt' => "شركة بلاد الرافدين للشحن والخدمات اللوجستية\nالسلام عليكم ورحمة الله وبركاته، عزيزي التاجر {name}،\nتم تسجيل تسديد لحساب {category}.\nالمبلغ المسدد: {amount} {currency}\nالتاريخ: {date}\nرقم السند: {reference}\nشكرًا لثقتكم بشركة بلاد الرافدين.",
            'shipment' => "شركة بلاد الرافدين للشحن والخدمات اللوجستية\nعزيزي التاجر {name}، مرفق بوليصة شحنتكم.\nكود الشحنة: {reference}\nتاريخ التسجيل: {date}\nعدد الطرود: {parcels}\nالوزن: {weight} كغم\nثقتكم هدفنا… وبضاعتكم أمانة.",
        ];
    }

    public static function forKind(string $kind): self
    {
        return self::firstOrCreate(['kind' => $kind], ['body' => self::defaults()[$kind]]);
    }

    public function fields(): array
    {
        preg_match_all('/\{([a-z_]+)\}/', $this->body, $matches);

        return array_values(array_unique($matches[1]));
    }

    public function providerBody(): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $this->body);
        foreach ($this->fields() as $index => $field) {
            $text = str_replace('{'.$field.'}', '{{'.($index + 1).'}}', $text);
        }

        return $text;
    }

    public function contentHash(): string
    {
        return hash('sha256', json_encode([$this->body, $this->provider_name, $this->language], JSON_UNESCAPED_UNICODE));
    }

    public function isVerified(): bool
    {
        return $this->verified_hash && hash_equals($this->verified_hash, $this->contentHash());
    }
}
