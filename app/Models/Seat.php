<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    // Koltuğun veritabanındaki hangi tabloyu kullandığını belirtir
    protected $table = 'seats';  // Eğer tablo adınız 'seats' değilse, burada tablonun adını yazabilirsiniz.

    // Koltuk modeline ait hangi alanlar kütüphanede kullanılabilir.
    protected $fillable = [
        'venue_id',   // Mekan ID'si
        'section',    // Bölüm
        'row',        // Satır
        'number',     // Koltuk numarası
        'status',     // Koltuk durumu (müsait, rezerve, vs.)
        'price'       // Koltuk fiyatı
    ];

    // Koltuğun, mekan (venue) ile olan ilişkisini tanımlar
    public function venue()
    {
        // Bir koltuğun bir mekanla ilişkili olduğu için 'belongsTo' ilişkisini kullanıyoruz
        return $this->belongsTo(Venue::class);
    }

    // Eğer tarihsel olarak ne zaman oluşturulduğunu istiyorsanız bu iki özelliği de ekleyebilirsiniz:
    public $timestamps = true; // Varsayılan olarak true, veritabanı zaman damgalarını kullanır
}
