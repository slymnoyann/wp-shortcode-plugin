jQuery(document).ready(function($) {
    $('#save_shortcode').on('click', function() {
        var name = $('#shortcode_name').val();
        var html = $('#html_content').val();
        
        if (!name || !html) {
            alert('Lütfen tüm alanları doldurun!');
            return;
        }
        
        // Türkçe karakter kontrolü
        if (/[çşğüöıİ]/i.test(name)) {
            alert('Kısa kod isminde Türkçe karakter kullanmayın!');
            return;
        }
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'save_html_shortcode',
                name: name,
                html: html
            },
            success: function(response) {
                if (response.success) {
                    $('#shortcode_display').text(response.data.shortcode);
                    $('#shortcode_result').removeClass('hidden');
                } else {
                    alert('Hata: ' + response.data);
                }
            },
            error: function() {
                alert('Bir hata oluştu! Lütfen tekrar deneyin.');
            }
        });
    });
    
    $('#copy_shortcode').on('click', function() {
        var shortcode = $('#shortcode_display').text();
        navigator.clipboard.writeText(shortcode).then(function() {
            alert('Kısa kod panoya kopyalandı!');
        }).catch(function() {
            alert('Kopyalama başarısız oldu! Lütfen kısa kodu manuel olarak kopyalayın.');
        });
    });
});