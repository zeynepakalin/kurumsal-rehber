# GitHub paylaşım incelemesi

Kişisel rehber kayıtları ve örnek kullanıcı hesapları SQL dosyalarından çıkarıldı. Eski veritabanı parolası da kaldırıldı; bağlantı bilgileri `DB_HOST`, `DB_USER`, `DB_PASSWORD` ve `DB_NAME` ortam değişkenlerinden okunuyor. `.env.example` yalnızca örnek değerleri gösterir; PHP bu dosyayı kendiliğinden yüklemez. Değerleri PHP/web sunucusunun ortamına tanımlayın.

## Herkese açık paylaşım öncesi

Bu proje halen gerçek şirket adları, logoları ve şirket web adresini içeriyor. Proje iş kapsamında geliştirildiyse kaynak kodunu ve marka varlıklarını kamuya açık paylaşma hakkınız olduğunu işvereninizle netleştirin; gerekirse marka ve şirket bilgilerini anonimleştirin.

Giriş sistemi parolaları düz metin olarak saklıyor ve karşılaştırıyor. Gerçek kullanıcılarla kesinlikle kullanmayın veya canlıya koymayın. Portföyde güvenli kod örneği olarak göstermek için önce `password_hash()` / `password_verify()` tabanlı hale getirin.

Kopyada hem eski hem güncel uygulama sürümleri ve boş bir SQL dosyası var; GitHub'a koymadan önce tek, güncel sürümü seçip temiz bir README ile kurulum ve özellikleri anlatın.

Daha önce GitHub'a gönderilmiş bir parola varsa değiştirin. Git geçmişinden silmek tek başına parolayı geçersiz kılmaz.
