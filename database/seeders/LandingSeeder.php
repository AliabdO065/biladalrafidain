<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LandingSeeder extends Seeder
{
    protected function t(string $ar, string $en, string $tr, string $he): string
    {
        return json_encode(['ar' => $ar, 'en' => $en, 'tr' => $tr, 'he' => $he], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function run(): void
    {
        if (DB::table('languages')->count() === 0) {
            DB::table('languages')->insert([
                ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'is_enabled' => true, 'is_default' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_enabled' => true, 'is_default' => false, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'is_enabled' => true, 'is_default' => false, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'he', 'name' => 'Hebrew', 'native_name' => 'עברית', 'is_enabled' => true, 'is_default' => false, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (DB::table('landing_settings')->count() === 0) {
            DB::table('landing_settings')->insert([
                'alert_banner_active' => true,
                'alert_banner_text' => $this->t(
                    'نوفر خدمة تتبع الشحنات على مدار الساعة — تابع شحنتك لحظة بلحظة.',
                    '24/7 shipment tracking available — follow your cargo every step of the way.',
                    '7/24 gönderi takibi mevcuttur — kargonuzu her adımda takip edin.',
                    'מעקב משלוחים 24/7 זמין — עקבו אחר המטען שלכם בכל שלב.'
                ),
                'logo_image' => null,
                'phone_display' => '+20 100 000 0000',
                'phone_href' => '+201000000000',
                'hero_headline' => $this->t(
                    'بلاد الرافدين لخدمات الشحن والتخليص الجمركي',
                    'Biladalrafidain Shipping & Customs Clearance Services',
                    'Biladalrafidain Nakliye ve Gümrük Hizmetleri',
                    'בלאד אל-רפידין לשירותי הובלה ושחרור מכס'
                ),
                'hero_subheadline' => $this->t(
                    'شحن كامل وجزئي، تخليص جمركي، ونقل ترانزيت عبر الموانئ المصرية — مع متابعة شحنتك لحظة بلحظة، بإدارة صدام وفريق متخصص.',
                    'Full and partial shipping, customs clearance, and transit through Egyptian ports — with live tracking of your shipment, led by Saddam and a specialized team.',
                    'Tam ve kısmi nakliye, gümrük işlemleri ve Mısır limanları üzerinden transit taşımacılık — Saddam ve uzman ekibi yönetiminde, gönderinizi anlık takip edin.',
                    'הובלה מלאה וחלקית, שחרור מכס, והובלת מעבר דרך נמלי מצרים — עם מעקב חי אחר המשלוח שלכם, בניהולו של סדאם וצוות מומחים.'
                ),
                'hero_cta_label' => $this->t('اتصل الآن', 'Call Now', 'Hemen Ara', 'התקשרו עכשיו'),
                'hero_image' => null,
                'rating_value' => 4.9,
                'rating_count' => 320,
                'about_owner_name' => 'صدام',
                'about_owner_photo' => null,
                'about_story' => $this->t(
                    'بلاد الرافدين شركة متخصصة في خدمات الشحن والتخليص الجمركي، تربط مصر بالدول العربية عبر شبكة موانئ وشركاء موثوقين. بقيادة صدام، نوفر لعملائنا من التجار والشركات حلول شحن متكاملة — من استلام البضاعة وحتى التسليم النهائي — بشفافية كاملة في الأسعار والمواعيد.',
                    'Biladalrafidain is a specialized shipping and customs clearance company, connecting Egypt with the Arab region through a trusted network of ports and partners. Led by Saddam, we provide traders and businesses with end-to-end shipping solutions — from pickup to final delivery — with full transparency on pricing and timelines.',
                    "Biladalrafidain, Mısır'ı güvenilir liman ve iş ortağı ağı aracılığıyla Arap bölgesine bağlayan uzman bir nakliye ve gümrük şirketidir. Saddam liderliğinde, tüccarlara ve işletmelere teslim alımdan nihai teslimata kadar uçtan uca nakliye çözümleri sunuyoruz — fiyatlandırma ve süreler konusunda tam şeffaflıkla.",
                    'בלאד אל-רפידין היא חברת הובלה ושחרור מכס מומחית, המחברת בין מצרים לעולם הערבי דרך רשת אמינה של נמלים ושותפים. בהנהגתו של סדאם, אנו מספקים לסוחרים ולעסקים פתרונות הובלה מקצה לקצה — מאיסוף ועד למסירה סופית — בשקיפות מלאה במחירים ובזמנים.'
                ),
                'trust_1_title' => $this->t('فريق شحن وتخليص جمركي معتمد', 'Certified Shipping & Customs Team', 'Sertifikalı Nakliye ve Gümrük Ekibi', 'צוות הובלה ושחרור מכס מוסמך'),
                'trust_1_text' => $this->t(
                    'فريقنا مرخص ومتمرس في إجراءات الجمارك والشحن الدولي.',
                    'Our team is licensed and experienced in customs procedures and international shipping.',
                    'Ekibimiz gümrük prosedürleri ve uluslararası nakliye konusunda lisanslı ve deneyimlidir.',
                    'הצוות שלנו מורשה ומנוסה בנהלי מכס והובלה בינלאומית.'
                ),
                'trust_2_title' => $this->t('جهة اتصال واحدة لمتابعة شحنتك', 'One Point of Contact', 'Tek İletişim Noktası', 'איש קשר אחד למעקב'),
                'trust_2_text' => $this->t(
                    'من استلام الطلب حتى التسليم — شخص واحد يتابع معك.',
                    'From order to delivery — one contact person follows up with you.',
                    'Siparişten teslimata kadar — sizinle tek bir kişi ilgilenir.',
                    'מקבלת ההזמנה ועד למסירה — איש קשר אחד מלווה אתכם.'
                ),
                'trust_3_title' => $this->t('أسعار واضحة قبل الشحن', 'Clear Pricing Before Shipping', 'Nakliyeden Önce Net Fiyatlandırma', 'תמחור ברור לפני ההובלה'),
                'trust_3_text' => $this->t(
                    'تعرف التكلفة الكاملة قبل إرسال شحنتك — بدون رسوم مفاجئة.',
                    'Know the full cost before sending your shipment — no hidden fees.',
                    'Gönderinizi yollamadan önce toplam maliyeti öğrenin — gizli ücret yok.',
                    'דעו את העלות המלאה לפני משלוח המטען — ללא עמלות נסתרות.'
                ),
                'trust_4_title' => $this->t('ضمان استرجاع الأموال', 'Money-Back Guarantee', 'Para İade Garantisi', 'אחריות להחזר כספי'),
                'trust_4_text' => $this->t(
                    'رضاك مضمون — وإذا لم نلتزم بالمتفق عليه نعيد لك أموالك.',
                    "Your satisfaction is guaranteed — if we don't deliver as agreed, we refund you.",
                    'Memnuniyetiniz garanti altındadır — anlaşmaya uymazsak paranızı iade ederiz.',
                    'שביעות רצונכם מובטחת — אם לא נעמוד בהסכם, נחזיר לכם את הכסף.'
                ),
                'company_name' => $this->t(
                    'بلاد الرافدين للشحن والخدمات اللوجستية',
                    'Biladalrafidain Shipping & Logistics',
                    'Biladalrafidain Nakliye ve Lojistik',
                    'בלאד אל-רפידין להובלה ולוגיסטיקה'
                ),
                'company_address' => '10th of Ramadan City, Egypt',
                'company_email' => 'info@biladalrafidain.com',
                'certifications_text' => $this->t(
                    'شريك معتمد لدى الجمارك المصرية · عضو غرفة الشحن والتخليص',
                    'Certified partner with Egyptian Customs · Member of the Shipping & Clearance Chamber',
                    'Mısır Gümrüğü onaylı ortak · Nakliye ve Gümrük Odası üyesi',
                    'שותף מוסמך ברשות המכס המצרית · חבר בלשכת ההובלה והשחרור'
                ),
                'footer_description' => $this->t(
                    'خدمات شحن وتخليص جمركي تربط مصر بالمنطقة العربية — بإدارة صدام وفريق متخصص.',
                    'Shipping and customs clearance services connecting Egypt with the Arab region — led by Saddam and a specialized team.',
                    "Mısır'ı Arap bölgesine bağlayan nakliye ve gümrük hizmetleri — Saddam ve uzman ekibi yönetiminde.",
                    'שירותי הובלה ושחרור מכס המחברים בין מצרים לעולם הערבי — בהנהגתו של סדאם וצוות מומחים.'
                ),
                'navbar_show_brand_text' => true,
                'navbar_brand_text' => $this->t(
                    'بلاد الرافدين',
                    'Biladalrafidain',
                    'Biladalrafidain',
                    'בלאד אל-רפידין'
                ),
                'impressum_url' => null,
                'privacy_url' => null,
                'terms_url' => null,
                'whatsapp_url' => 'https://wa.me/201000000000',
                'hero_badge_text' => $this->t(
                    'بلاد الرافدين للخدمات اللوجستية · شحن وتخليص جمركي',
                    'Biladalrafidain Logistics · Shipping & Customs Clearance',
                    'Biladalrafidain Lojistik · Nakliye ve Gümrük',
                    'בלאד אל-רפידין לוגיסטיקה · הובלה ושחרור מכס'
                ),
                'hero_secondary_cta_label' => $this->t('تتبع شحنتك', 'Track Your Shipment', 'Gönderini Takip Et', 'עקבו אחר המשלוח'),
                'about_eyebrow' => $this->t('من نحن', 'About Us', 'Hakkımızda', 'אודותינו'),
                'about_heading' => $this->t(
                    'شريك لوجستي تثق به المنطقة',
                    'A Logistics Partner Trusted Across the Region',
                    'Bölgede Güvenilen Bir Lojistik Ortağı',
                    'שותף לוגיסטי שהאזור סומך עליו'
                ),
                'services_eyebrow' => $this->t('خدماتنا', 'Our Services', 'Hizmetlerimiz', 'השירותים שלנו'),
                'services_heading' => $this->t(
                    'كل ما تحتاجه شحنتك في مكان واحد',
                    'Everything Your Shipment Needs, In One Place',
                    'Gönderinizin İhtiyacı Olan Her Şey Tek Yerde',
                    'כל מה שהמשלוח שלכם צריך, במקום אחד'
                ),
                'services_subheading' => $this->t(
                    'من استلام البضاعة وحتى التسليم النهائي.',
                    'From pickup to customs clearance to final delivery.',
                    'Teslim alımdan gümrük işlemlerine ve nihai teslimata kadar.',
                    'מאיסוף ועד לשחרור מכס ומסירה סופית.'
                ),
                'steps_eyebrow' => $this->t('خطوات بسيطة', 'Simple Process', 'Basit Süreç', 'תהליך פשוט'),
                'steps_heading' => $this->t(
                    'شحنتك في 3 خطوات سهلة',
                    'Your Shipment in 3 Easy Steps',
                    'Gönderiniz 3 Kolay Adımda',
                    'המשלוח שלכם ב-3 שלבים פשוטים'
                ),
                'comparison_eyebrow' => $this->t('ليه تختارنا', 'Why Choose Us', 'Neden Bizi Seçmelisiniz', 'למה לבחור בנו'),
                'comparison_heading' => $this->t(
                    'شريك موثوق مقابل وسطاء غير موثوقين',
                    'A Trusted Logistics Partner vs. Unreliable Brokers',
                    'Güvenilir Bir Lojistik Ortağı ve Güvenilmez Aracılar',
                    'שותף לוגיסטי אמין מול מתווכים לא אמינים'
                ),
                'comparison_subheading' => $this->t(
                    'للأسف لا كل وسطاء الشحن موثوقون. إليك كيف تفرق.',
                    'Not all shipping brokers are reliable. Here is how to tell the difference.',
                    'Ne yazık ki her nakliye aracısı güvenilir değildir. Farkı böyle anlarsınız.',
                    'לצערנו לא כל מתווכי ההובלה אמינים. כך תדעו להבדיל.'
                ),
                'reviews_eyebrow' => $this->t('آراء عملائنا', 'Client Testimonials', 'Müşteri Yorumları', 'המלצות לקוחות'),
                'reviews_heading' => $this->t('ماذا يقول عملاؤنا', 'What Our Clients Say', 'Müşterilerimiz Ne Diyor', 'מה הלקוחות שלנו אומרים'),
                'faq_eyebrow' => $this->t('الأسئلة الشائعة', 'FAQ', 'SSS', 'שאלות נפוצות'),
                'faq_heading' => $this->t('معلومات مفيدة', 'Good to Know', 'Bilmekte Fayda Var', 'טוב לדעת'),
                'callback_eyebrow' => $this->t('لديك استفسار؟', 'Have a Question?', 'Bir Sorunuz mu Var?', 'יש לכם שאלה?'),
                'callback_heading' => $this->t('اطلب معاودة الاتصال', 'Request a Callback', 'Geri Arama Talep Edin', 'בקשו שנחזור אליכם'),
                'callback_subtext' => $this->t(
                    'أخبرنا بإيجاز عن احتياجك — سنعاود الاتصال بك قريبًا.',
                    'Tell us briefly what you need — we will call you back shortly.',
                    'İhtiyacınızı kısaca belirtin — sizi kısa sürede arayacağız.',
                    'ספרו לנו בקצרה מה אתם צריכים — נחזור אליכם בקרוב.'
                ),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('landing_stats')->count() === 0) {
            DB::table('landing_stats')->insert([
                ['value' => '+8', 'label' => $this->t('سنوات خبرة', 'years of experience', 'yıllık deneyim', 'שנות ניסיון'), 'icon' => 'fa-award', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['value' => '+5000', 'label' => $this->t('شحنة تم تسليمها', 'shipments delivered', 'teslim edilen gönderi', 'משלוחים שנמסרו'), 'icon' => 'fa-box', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['value' => '24/7', 'label' => $this->t('دعم عملاء', 'customer support', 'müşteri desteği', 'תמיכת לקוחות'), 'icon' => 'fa-headset', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['value' => '4.9 ★', 'label' => $this->t('تقييم العملاء', 'average client rating', 'müşteri değerlendirmesi', 'דירוג לקוחות'), 'icon' => 'fa-star', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (DB::table('landing_services')->count() === 0) {
            DB::table('landing_services')->insert([
                [
                    'title' => $this->t('شحن كامل وجزئي', 'Full & Partial Shipping', 'Tam ve Kısmi Nakliye', 'הובלה מלאה וחלקית'),
                    'description' => $this->t(
                        'نوفر خدمة الشحن الكامل والجزئي لجميع أنحاء المنطقة، بأسعار تنافسية ومواعيد دقيقة.',
                        'We offer full and partial shipping across the region, with competitive prices and accurate schedules.',
                        'Bölge genelinde rekabetçi fiyatlarla ve doğru zamanlamayla tam ve kısmi nakliye sunuyoruz.',
                        'אנו מציעים הובלה מלאה וחלקית ברחבי האזור, במחירים תחרותיים ובזמנים מדויקים.'
                    ),
                    'icon' => 'fa-truck-fast', 'image' => null, 'sort_order' => 1, 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'title' => $this->t('التخليص الجمركي', 'Customs Clearance', 'Gümrük İşlemleri', 'שחרור מכס'),
                    'description' => $this->t(
                        'نتولى كافة إجراءات التخليص الجمركي للاستيراد والتصدير نيابة عنك بسرعة واحترافية.',
                        'We handle all import and export customs clearance procedures on your behalf, quickly and professionally.',
                        'İthalat ve ihracat gümrük işlemlerinin tamamını sizin adınıza hızlı ve profesyonelce yürütüyoruz.',
                        'אנו מטפלים בכל נהלי שחרור המכס ליבוא וליצוא בשמכם, במהירות ובמקצועיות.'
                    ),
                    'icon' => 'fa-passport', 'image' => null, 'sort_order' => 2, 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'title' => $this->t('أذونات الاستيراد المؤقت', 'Temporary Import Permits', 'Geçici İthalat İzinleri', 'היתרי יבוא זמני'),
                    'description' => $this->t(
                        'نساعدك في استخراج أذونات الاستيراد المؤقت بسهولة وفي أقصر وقت ممكن.',
                        'We help you obtain temporary import permits easily and in the shortest possible time.',
                        'Geçici ithalat izinlerini kolayca ve en kısa sürede almanıza yardımcı oluyoruz.',
                        'אנו עוזרים לכם להשיג היתרי יבוא זמני בקלות ובזמן הקצר ביותר.'
                    ),
                    'icon' => 'fa-file-invoice', 'image' => null, 'sort_order' => 3, 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'title' => $this->t('المناطق الحرة', 'Free Trade Zone Operations', 'Serbest Bölge İşlemleri', 'פעילות באזור סחר חופשי'),
                    'description' => $this->t(
                        'ندير عمليات الشحن والتخزين داخل المناطق التجارية الحرة بكفاءة عالية.',
                        'We manage shipping and storage operations within free trade zones with high efficiency.',
                        'Serbest ticaret bölgelerindeki nakliye ve depolama işlemlerini yüksek verimlilikle yönetiyoruz.',
                        'אנו מנהלים פעולות הובלה ואחסון בתוך אזורי סחר חופשי ביעילות גבוהה.'
                    ),
                    'icon' => 'fa-warehouse', 'image' => null, 'sort_order' => 4, 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'title' => $this->t('نقل الترانزيت عبر الموانئ المصرية', 'Transit Transport via Egyptian Ports', 'Mısır Limanları Üzerinden Transit Taşımacılık', 'הובלת מעבר דרך נמלי מצרים'),
                    'description' => $this->t(
                        'ننقل شحناتك عبر شبكة الموانئ المصرية إلى وجهتها النهائية بأمان.',
                        'We transport your shipments through the Egyptian port network to their final destination safely.',
                        'Gönderilerinizi Mısır liman ağı üzerinden güvenle nihai varış noktasına taşıyoruz.',
                        'אנו מובילים את המשלוחים שלכם דרך רשת הנמלים המצרית ליעדם הסופי בבטחה.'
                    ),
                    'icon' => 'fa-ship', 'image' => null, 'sort_order' => 5, 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'title' => $this->t('خدمات فحص التصدير', 'Export Inspection Services', 'İhracat Denetim Hizmetleri', 'שירותי בדיקת יצוא'),
                    'description' => $this->t(
                        'نقدم خدمات الفحص والمطابقة للشحنات قبل التصدير لضمان استيفاء كافة المعايير.',
                        'We provide inspection and compliance services for shipments before export to ensure all standards are met.',
                        'İhracat öncesi gönderiler için tüm standartların karşılandığından emin olmak üzere denetim ve uygunluk hizmetleri sunuyoruz.',
                        'אנו מספקים שירותי בדיקה ותאימות למשלוחים לפני היצוא כדי להבטיח עמידה בכל התקנים.'
                    ),
                    'icon' => 'fa-magnifying-glass', 'image' => null, 'sort_order' => 6, 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ],
            ]);
        }

        if (DB::table('landing_steps')->count() === 0) {
            DB::table('landing_steps')->insert([
                [
                    'step_number' => 1,
                    'title' => $this->t('تواصل معنا وحدد شحنتك', 'Contact Us & Define Your Shipment', 'Bizimle İletişime Geçin ve Gönderinizi Belirleyin', 'צרו קשר והגדירו את המשלוח'),
                    'description' => $this->t(
                        'أخبرنا بتفاصيل الشحنة ووجهتها — فريقنا يرد عليك خلال دقائق.',
                        'Tell us your shipment details and destination — our team responds within minutes.',
                        'Gönderi detaylarınızı ve varış noktanızı bize bildirin — ekibimiz dakikalar içinde yanıt verir.',
                        'ספרו לנו את פרטי המשלוח והיעד — הצוות שלנו יגיב תוך דקות.'
                    ),
                    'image' => null, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'step_number' => 2,
                    'title' => $this->t('نقوم بالشحن والتخليص الجمركي', 'We Handle Shipping & Customs', 'Nakliye ve Gümrük İşlemlerini Biz Hallederiz', 'אנו מטפלים בהובלה ובמכס'),
                    'description' => $this->t(
                        'نتولى كل الإجراءات اللازمة من الشحن حتى التخليص الجمركي نيابة عنك.',
                        'We take care of everything from shipping to customs clearance on your behalf.',
                        'Nakliyeden gümrük işlemlerine kadar her şeyi sizin adınıza hallederiz.',
                        'אנו דואגים לכל הדרוש, מהובלה ועד שחרור מכס, בשמכם.'
                    ),
                    'image' => null, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'step_number' => 3,
                    'title' => $this->t('تتبع شحنتك حتى الاستلام', 'Track Your Shipment Until Delivery', 'Teslimata Kadar Gönderinizi Takip Edin', 'עקבו אחר המשלוח עד למסירה'),
                    'description' => $this->t(
                        'تابع حالة شحنتك لحظة بلحظة عبر صفحة التتبع الخاصة بنا حتى تصل بأمان.',
                        "Follow your shipment's status live through our tracking page until it arrives safely.",
                        'Güvenle varana kadar takip sayfamız üzerinden gönderinizin durumunu anlık izleyin.',
                        'עקבו אחר סטטוס המשלוח בזמן אמת דרך עמוד המעקב שלנו עד שיגיע בבטחה.'
                    ),
                    'image' => null, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now(),
                ],
            ]);
        }

        if (DB::table('landing_comparisons')->count() === 0) {
            DB::table('landing_comparisons')->insert([
                [
                    'criterion' => $this->t('من يتولى شحنتك؟', 'Who Handles Your Shipment?', 'Gönderinizi Kim Yönetiyor?', 'מי מטפל במשלוח שלכם?'),
                    'us_value' => $this->t('فريق مرخص ومتابعة مباشرة من صدام وفريقه', 'A licensed team with direct oversight from Saddam and his team', 'Saddam ve ekibinin doğrudan gözetiminde lisanslı bir ekip', 'צוות מוסמך בפיקוח ישיר של סדאם וצוותו'), 'us_is_positive' => true,
                    'them_value' => $this->t('وسيط مجهول بدون ضمانات', 'An anonymous broker with no guarantees', 'Garantisi olmayan anonim bir aracı', 'מתווך אנונימי ללא ערבויות'), 'them_is_positive' => false,
                    'sort_order' => 1, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'criterion' => $this->t('التخليص الجمركي', 'Customs Clearance', 'Gümrük İşlemleri', 'שחרור מכס'),
                    'us_value' => $this->t('إجراءات رسمية وسريعة مع الجمارك المصرية', 'Fast, official procedures with Egyptian Customs', 'Mısır Gümrüğü ile hızlı, resmi prosedürler', 'נהלים רשמיים ומהירים מול המכס המצרי'), 'us_is_positive' => true,
                    'them_value' => $this->t('تأخيرات وغرامات غير متوقعة', 'Delays and unexpected fines', 'Gecikmeler ve beklenmedik cezalar', 'עיכובים וקנסות בלתי צפויים'), 'them_is_positive' => false,
                    'sort_order' => 2, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'criterion' => $this->t('التسعير', 'Pricing', 'Fiyatlandırma', 'תמחור'),
                    'us_value' => $this->t('سعر واضح ومكتوب قبل الشحن', 'Clear, written price before shipping', 'Nakliyeden önce net, yazılı fiyat', 'מחיר ברור בכתב לפני ההובלה'), 'us_is_positive' => true,
                    'them_value' => $this->t('رسوم إضافية مفاجئة بعد الشحن', 'Surprise extra fees after shipping', 'Nakliye sonrası sürpriz ek ücretler', 'עמלות נוספות מפתיעות אחרי ההובלה'), 'them_is_positive' => false,
                    'sort_order' => 3, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'criterion' => $this->t('تتبع الشحنة', 'Shipment Tracking', 'Gönderi Takibi', 'מעקב משלוח'),
                    'us_value' => $this->t('تتبع لحظي عبر الموقع', 'Live tracking through our website', 'Web sitemiz üzerinden anlık takip', 'מעקב חי דרך האתר שלנו'), 'us_is_positive' => true,
                    'them_value' => $this->t('لا توجد وسيلة لمعرفة موقع شحنتك', 'No way to know where your shipment is', 'Gönderinizin nerede olduğunu bilmenin bir yolu yok', 'אין דרך לדעת היכן נמצא המשלוח'), 'them_is_positive' => false,
                    'sort_order' => 4, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'criterion' => $this->t('الخلفية التجارية', 'Business Background', 'Ticari Geçmiş', 'רקע עסקי'),
                    'us_value' => $this->t('شركة مسجلة برئاسة صدام، بسجل تجاري وخبرة موثقة', 'A registered company led by Saddam, with a documented commercial track record', 'Saddam liderliğinde, belgeli ticari geçmişe sahip kayıtlı bir şirket', 'חברה רשומה בהנהגת סדאם, עם רקע מסחרי מתועד'), 'us_is_positive' => true,
                    'them_value' => $this->t('شركات وهمية قصيرة العمر', 'Short-lived shell companies', 'Kısa ömürlü paravan şirketler', 'חברות קש קצרות מועד'), 'them_is_positive' => false,
                    'sort_order' => 5, 'created_at' => now(), 'updated_at' => now(),
                ],
            ]);
        }

        if (DB::table('landing_reviews')->count() === 0) {
            $reviews = [
                ['محمد العبيدي', 5, '-20 days',
                    'تعاملت مع بلاد الرافدين لشحن بضاعة من مصر للعراق، والتخليص الجمركي تم بسرعة غير متوقعة. فريق محترف جدًا.',
                    'I worked with Biladalrafidain to ship goods from Egypt to Iraq, and customs clearance was done faster than expected. Very professional team.',
                    "Mısır'dan Irak'a mal göndermek için Biladalrafidain ile çalıştım ve gümrük işlemleri beklenenden hızlı tamamlandı. Çok profesyonel bir ekip.",
                    'עבדתי עם בלאד אל-רפידין כדי לשלוח סחורה ממצרים לעיראק, ושחרור המכס בוצע מהר מהצפוי. צוות מקצועי מאוד.'],
                ['سارة حسن', 5, '-35 days',
                    'خدمة تتبع الشحنة ممتازة، كنت أعرف بالظبط فين شحنتي في كل لحظة. أنصح بيهم بشدة.',
                    'The shipment tracking service is excellent — I knew exactly where my cargo was at every moment. Highly recommend them.',
                    'Gönderi takip hizmeti mükemmel — kargomun her an tam olarak nerede olduğunu biliyordum. Kesinlikle tavsiye ederim.',
                    'שירות מעקב המשלוחים מצוין — ידעתי בדיוק איפה המטען שלי בכל רגע. ממליצה בחום.'],
                ['أحمد ياسين', 4, '-50 days',
                    'أسعار عادلة ومواعيد شحن محترمة. كان هناك تأخير بسيط في الرد على الهاتف لكن الخدمة النهائية كانت ممتازة.',
                    'Fair prices and reliable shipping schedules. There was a slight delay answering the phone, but the final service was excellent.',
                    'Adil fiyatlar ve güvenilir nakliye programları. Telefona cevap vermede küçük bir gecikme oldu ama nihai hizmet mükemmeldi.',
                    'מחירים הוגנים ולוחות זמנים אמינים להובלה. הייתה עיכוב קל במענה בטלפון, אבל השירות הסופי היה מצוין.'],
                ['ليلى كريم', 5, '-65 days',
                    'أول مرة أشحن بضاعة تجارية بكميات كبيرة، وفريق بلاد الرافدين سهّل عليّ كل الإجراءات الجمركية.',
                    'It was my first time shipping large commercial quantities, and the Biladalrafidain team made all the customs procedures easy for me.',
                    'Büyük miktarda ticari mal göndermek benim için ilk seferdi ve Biladalrafidain ekibi tüm gümrük işlemlerini benim için kolaylaştırdı.',
                    'זו הייתה הפעם הראשונה שלי לשלוח כמויות מסחריות גדולות, וצוות בלאד אל-רפידין הקל עליי בכל נהלי המכס.'],
                ['عمر الشمري', 5, '-80 days',
                    'التعامل مع صدام وفريقه كان احترافي جدًا، ومتابعة مستمرة لحد ما استلمت شحنتي بالكامل.',
                    'Dealing with Saddam and his team was very professional, with continuous follow-up until I received my shipment in full.',
                    'Saddam ve ekibiyle çalışmak çok profesyoneldi, gönderimi tamamen teslim alana kadar sürekli takip vardı.',
                    'העבודה עם סדאם וצוותו הייתה מאוד מקצועית, עם מעקב מתמשך עד שקיבלתי את המשלוח שלי במלואו.'],
                ['نور الدين فاروق', 4, '-95 days',
                    'خدمة جيدة جدًا في نقل الترانزيت عبر الموانئ المصرية، ووصلت الشحنة في الموعد المحدد.',
                    'Very good service for transit shipping through Egyptian ports, and the shipment arrived on schedule.',
                    'Mısır limanları üzerinden transit nakliye için çok iyi bir hizmet ve gönderi zamanında ulaştı.',
                    'שירות טוב מאוד להובלת מעבר דרך נמלי מצרים, והמשלוח הגיע בזמן.'],
            ];
            $rows = [];
            foreach ($reviews as $i => [$name, $rating, $when, $ar, $en, $tr, $he]) {
                $rows[] = [
                    'author_name' => $name,
                    'author_photo' => null,
                    'rating' => $rating,
                    'review_date' => now()->modify($when)->toDateString(),
                    'review_text' => $this->t($ar, $en, $tr, $he),
                    'is_placeholder' => true,
                    'sort_order' => $i + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('landing_reviews')->insert($rows);
        }

        if (DB::table('landing_faqs')->count() === 0) {
            DB::table('landing_faqs')->insert([
                [
                    'question' => $this->t('كيف أتتبع شحنتي؟', 'How do I track my shipment?', 'Gönderimi nasıl takip ederim?', 'איך אני עוקב אחר המשלוח שלי?'),
                    'answer' => $this->t(
                        'ادخل على صفحة "تتبع الشحنة" في أعلى الموقع وأدخل كود الشحنة الذي استلمته منا لمعرفة الحالة لحظة بلحظة.',
                        "Go to the 'Track Shipment' page at the top of the site and enter the tracking code we gave you to see live status.",
                        "Sitenin üst kısmındaki 'Gönderi Takip' sayfasına gidin ve size verdiğimiz takip kodunu girerek anlık durumu görün.",
                        "היכנסו לעמוד 'מעקב משלוח' בראש האתר והזינו את קוד המעקב שקיבלתם מאיתנו כדי לראות סטטוס בזמן אמת."
                    ),
                    'sort_order' => 1, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'question' => $this->t('كم يستغرق التخليص الجمركي؟', 'How long does customs clearance take?', 'Gümrük işlemleri ne kadar sürer?', 'כמה זמן לוקח שחרור המכס?'),
                    'answer' => $this->t(
                        'يختلف حسب نوع البضاعة والوجهة، لكن فريقنا يعمل على إنجاز الإجراءات في أسرع وقت ممكن ونخبرك بالمدة المتوقعة قبل البدء.',
                        'It varies by cargo type and destination, but our team works to complete procedures as fast as possible and tells you the expected duration before starting.',
                        'Kargo türüne ve varış noktasına göre değişir, ancak ekibimiz işlemleri mümkün olan en kısa sürede tamamlamak için çalışır ve başlamadan önce beklenen süreyi size bildirir.',
                        'זה משתנה לפי סוג המטען והיעד, אך הצוות שלנו פועל להשלים את הנהלים במהירות האפשרית ומודיע לכם על משך הזמן הצפוי לפני ההתחלה.'
                    ),
                    'sort_order' => 2, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'question' => $this->t('هل يمكن شحن كميات صغيرة (شحن جزئي)؟', 'Can I ship small quantities (partial shipping)?', 'Küçük miktarlarda gönderi (kısmi nakliye) yapabilir miyim?', 'האם ניתן לשלוח כמויות קטנות (הובלה חלקית)?'),
                    'answer' => $this->t(
                        'نعم، نوفر خدمة الشحن الجزئي بجانب الشحن الكامل لتناسب احتياجات جميع عملائنا.',
                        "Yes, we offer partial shipping alongside full shipping to suit all our clients' needs.",
                        'Evet, tüm müşterilerimizin ihtiyaçlarına uygun olarak tam nakliyenin yanı sıra kısmi nakliye de sunuyoruz.',
                        'כן, אנו מציעים הובלה חלקית לצד הובלה מלאה כדי להתאים לצרכי כל לקוחותינו.'
                    ),
                    'sort_order' => 3, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'question' => $this->t('كيف يمكنني الدفع؟', 'How can I pay?', 'Nasıl ödeme yapabilirim?', 'איך אני יכול לשלם?'),
                    'answer' => $this->t(
                        'نوفر طرق دفع متعددة تشمل التحويل البنكي والدفع النقدي مقابل فاتورة رسمية — سيتم الاتفاق على التفاصيل مع فريقنا.',
                        'We offer multiple payment methods including bank transfer and cash against an official invoice — details are agreed with our team.',
                        'Banka havalesi ve resmi fatura karşılığında nakit dahil olmak üzere birden fazla ödeme yöntemi sunuyoruz — detaylar ekibimizle kararlaştırılır.',
                        'אנו מציעים מספר אמצעי תשלום כולל העברה בנקאית ומזומן מול חשבונית רשמית — הפרטים ייקבעו מול הצוות שלנו.'
                    ),
                    'sort_order' => 4, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'question' => $this->t('هل هناك ضمان على الشحنة؟', 'Is there a guarantee on my shipment?', 'Gönderim için bir garanti var mı?', 'האם יש אחריות על המשלוח?'),
                    'answer' => $this->t(
                        'نعم، نقدم ضمان استرجاع الأموال في حال عدم التزامنا بالاتفاق المتفق عليه معك.',
                        "Yes, we offer a money-back guarantee if we fail to meet what was agreed with you.",
                        'Evet, sizinle üzerinde anlaştığımız şeyi yerine getiremezsek para iade garantisi sunuyoruz.',
                        'כן, אנו מציעים אחריות להחזר כספי אם לא נעמוד במה שסוכם איתכם.'
                    ),
                    'sort_order' => 5, 'created_at' => now(), 'updated_at' => now(),
                ],
            ]);
        }

        if (DB::table('landing_policies')->count() === 0) {
            DB::table('landing_policies')->insert([
                [
                    'slug' => 'privacy-policy',
                    'title' => $this->t('سياسة الخصوصية', 'Privacy Policy', 'Gizlilik Politikası', 'מדיניות פרטיות'),
                    'body' => $this->t(
                        'نحترم خصوصية عملائنا ولا نشارك بياناتهم الشخصية مع أي جهة خارجية إلا في الحدود اللازمة لإتمام إجراءات الشحن والتخليص الجمركي.',
                        'We respect our clients\' privacy and do not share their personal data with any third party except as necessary to complete shipping and customs procedures.',
                        'Müşterilerimizin gizliliğine saygı duyuyoruz ve kişisel verilerini, nakliye ve gümrük işlemlerini tamamlamak için gerekli olmadıkça üçüncü taraflarla paylaşmıyoruz.',
                        'אנו מכבדים את פרטיות לקוחותינו ואיננו משתפים את המידע האישי שלהם עם צד שלישי אלא במידה הנדרשת להשלמת נהלי ההובלה והמכס.'
                    ),
                    'sort_order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'slug' => 'shipping-policy',
                    'title' => $this->t('سياسة الشحن', 'Shipping Policy', 'Nakliye Politikası', 'מדיניות הובלה'),
                    'body' => $this->t(
                        'نلتزم بالمواعيد المتفق عليها مع العميل، ويتم إخطار العميل فورًا بأي تغيير في موعد التسليم مع توضيح الأسباب.',
                        'We commit to the delivery dates agreed with the client, and the client is notified immediately of any change in delivery schedule along with the reasons.',
                        'Müşteri ile kararlaştırılan teslimat tarihlerine bağlı kalıyoruz ve teslimat programında herhangi bir değişiklik olması durumunda müşteri nedenleriyle birlikte derhal bilgilendirilir.',
                        'אנו מתחייבים לתאריכי המסירה שסוכמו עם הלקוח, והלקוח מקבל הודעה מיידית על כל שינוי בלוח הזמנים יחד עם הסיבות.'
                    ),
                    'sort_order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'slug' => 'refund-policy',
                    'title' => $this->t('سياسة استرجاع الأموال', 'Refund Policy', 'İade Politikası', 'מדיניות החזרים'),
                    'body' => $this->t(
                        'في حال عدم التزامنا بالشروط المتفق عليها، يحق للعميل استرجاع المبلغ المدفوع بالكامل خلال 14 يومًا من تاريخ الإبلاغ.',
                        'If we fail to meet the agreed terms, the client is entitled to a full refund of the amount paid within 14 days of reporting the issue.',
                        'Anlaşılan şartlara uymamamız durumunda, müşteri sorunu bildirdiği tarihten itibaren 14 gün içinde ödenen tutarın tamamını iade alma hakkına sahiptir.',
                        'אם לא נעמוד בתנאים המוסכמים, הלקוח זכאי להחזר מלא של הסכום ששולם תוך 14 יום ממועד הדיווח.'
                    ),
                    'sort_order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'slug' => 'terms-of-service',
                    'title' => $this->t('الشروط والأحكام', 'Terms of Service', 'Hizmet Şartları', 'תנאי שימוש'),
                    'body' => $this->t(
                        'استخدامك لخدماتنا يعني موافقتك على الشروط والأحكام الخاصة بنا، بما في ذلك الالتزام بتقديم بيانات صحيحة عن الشحنة والامتثال للقوانين الجمركية المعمول بها.',
                        'Using our services means you agree to our terms and conditions, including committing to provide accurate shipment information and complying with applicable customs laws.',
                        'Hizmetlerimizi kullanmanız, gönderi hakkında doğru bilgi verme taahhüdü ve geçerli gümrük yasalarına uyma dahil olmak üzere şartlarımızı kabul ettiğiniz anlamına gelir.',
                        'השימוש בשירותינו משמעו הסכמתכם לתנאים שלנו, לרבות התחייבות למסור מידע מדויק על המשלוח ולעמוד בחוקי המכס החלים.'
                    ),
                    'sort_order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                ],
            ]);
        }

        if (DB::table('landing_shipments')->count() === 0) {
            $shipment1 = DB::table('landing_shipments')->insertGetId([
                'tracking_code' => 'BR-2026-00123',
                'sender_name' => 'شركة النور للتجارة',
                'receiver_name' => 'محمد العبيدي',
                'origin' => 'القاهرة، مصر',
                'destination' => 'بغداد، العراق',
                'status' => 'in_transit',
                'estimated_delivery' => now()->addDays(5)->toDateString(),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('landing_shipment_events')->insert([
                [
                    'shipment_id' => $shipment1,
                    'status' => 'received',
                    'note' => $this->t('تم استلام الشحنة في المستودع بالقاهرة', 'Shipment received at Cairo warehouse', 'Gönderi Kahire deposunda teslim alındı', 'המשלוח התקבל במחסן בקהיר'),
                    'happened_at' => now()->subDays(4),
                    'sort_order' => 1, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'shipment_id' => $shipment1,
                    'status' => 'customs',
                    'note' => $this->t('جاري التخليص الجمركي', 'Customs clearance in progress', 'Gümrük işlemleri devam ediyor', 'שחרור מכס בתהליך'),
                    'happened_at' => now()->subDays(3),
                    'sort_order' => 2, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'shipment_id' => $shipment1,
                    'status' => 'in_transit',
                    'note' => $this->t('الشحنة في الطريق عبر ميناء بورسعيد', 'Shipment in transit via Port Said', 'Gönderi Port Said limanı üzerinden yolda', 'המשלוח בדרך דרך נמל פורט סעיד'),
                    'happened_at' => now()->subDay(),
                    'sort_order' => 3, 'created_at' => now(), 'updated_at' => now(),
                ],
            ]);

            $shipment2 = DB::table('landing_shipments')->insertGetId([
                'tracking_code' => 'BR-2026-00456',
                'sender_name' => 'مصنع الأمل للملابس',
                'receiver_name' => 'ليلى كريم',
                'origin' => 'الإسكندرية، مصر',
                'destination' => 'عمّان، الأردن',
                'status' => 'delivered',
                'estimated_delivery' => now()->subDay()->toDateString(),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('landing_shipment_events')->insert([
                [
                    'shipment_id' => $shipment2,
                    'status' => 'received',
                    'note' => $this->t('تم استلام الشحنة', 'Shipment received', 'Gönderi teslim alındı', 'המשלוח התקבל'),
                    'happened_at' => now()->subDays(7),
                    'sort_order' => 1, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'shipment_id' => $shipment2,
                    'status' => 'customs',
                    'note' => $this->t('تم التخليص الجمركي بنجاح', 'Customs clearance completed successfully', 'Gümrük işlemleri başarıyla tamamlandı', 'שחרור המכס הושלם בהצלחה'),
                    'happened_at' => now()->subDays(6),
                    'sort_order' => 2, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'shipment_id' => $shipment2,
                    'status' => 'in_transit',
                    'note' => $this->t('الشحنة في الطريق إلى عمّان', 'Shipment in transit to Amman', 'Gönderi Amman\'a doğru yolda', 'המשלוח בדרך לעמאן'),
                    'happened_at' => now()->subDays(3),
                    'sort_order' => 3, 'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'shipment_id' => $shipment2,
                    'status' => 'delivered',
                    'note' => $this->t('تم تسليم الشحنة للمستلم بنجاح', 'Shipment successfully delivered to the receiver', 'Gönderi alıcıya başarıyla teslim edildi', 'המשלוח נמסר בהצלחה לנמען'),
                    'happened_at' => now()->subDay(),
                    'sort_order' => 4, 'created_at' => now(), 'updated_at' => now(),
                ],
            ]);
        }
    }
}
