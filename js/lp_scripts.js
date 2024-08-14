jQuery(function ($) {

    function vendorClick() {
        $('.lp-vendor-block .clickable.active').removeClass('active');
        $($(this).data('mapElement')).addClass('active');
        showVendor(this);
    }

    function tableClick() {
        if(!$(this).data('target') || $(this).data('target').length <= 0) return;
        $('.lp-vendor-block .clickable.active').removeClass('active');
        $(this).addClass('active');
        if($(this).data('target').length > 1){
            showVendorChoice($(this).data('target'));
        } else {
            showVendor($(this).data('target')[0]);
        }
    }

    function showVendorList() {
        $('.lp-vendor-block .lp-vendor-pick, .lp-vendor-block .lp-vendor-item').hide();
        $('.lp-vendor-block .lp-vendor-list').css('display', '');
        $('.lp-vendor-block .clickable.active').removeClass('active');
    }

    function showVendorChoice(choicesEls) {
        $('.lp-vendor-block .lp-vendor-list, .lp-vendor-block .lp-vendor-item').hide();
        $('.lp-vendor-block .lp-vendor-pick').show();
        let target = $('.lp-vendor-block .lp-vendor-pick .lp-vendor-content').empty();
        for(let i in choicesEls){
            if(choicesEls.hasOwnProperty(i)) {
                target.append($(choicesEls[i]).clone(true));
            }
        }
        maybeScrollToTarget(target);
    }

    function showVendor(vendorEl) {
        $('.lp-vendor-block .lp-vendor-pick, .lp-vendor-block .lp-vendor-list').hide();
        $('.lp-vendor-block .lp-vendor-item').show();
        let target = $('.lp-vendor-block .lp-vendor-item .lp-vendor-content').empty();
        let img = $(vendorEl).find('.lp-vendor-image');
        if(img.length > 0){
            target.append(img.clone());
        }
        target.append($(vendorEl).find('.lp-title-flex').clone())
            .append($(vendorEl).find('.lp-table-name').clone().show())
            .append($(vendorEl).find('.lp-description').clone().show());
        maybeScrollToTarget(target);
    }

    function maybeScrollToTarget(target){
        if(window.matchMedia("(max-width: 1024px)").matches) {
            window.scrollTo({
                top: target.offset().top - 20,
                behavior: "smooth"
            });
        }
    }

    $('.lp-vendor-block .lp-vendor').on('click', vendorClick).each(function () {
        if($(this).data() && $(this).data().hasOwnProperty('mapElement')){
            let svgEl = $($(this).data().mapElement);
            if(svgEl.length > 0){
                let data = svgEl.data('target');
                if (!data) data = [];
                data.push(this);
                svgEl.data('target', data);
            }
        }
    });
    $('.lp-vendor-block .clickable').on('click', tableClick);
    $('.lp-btn-back').on('click', showVendorList)
});