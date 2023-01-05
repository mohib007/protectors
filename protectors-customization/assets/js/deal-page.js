var RespondReturn = [];
jQuery( document ).ready(function($) {
    $("#export-deals").after("<a onClick='PrintDeal();' class='add-new-h2' target='_blank'>Print</a>");
});

function PrintDeal(){
    var logo = jQuery('.clientside-menu-logo').attr('src');
    var dataDeal = [];
    var addresses = jQuery('.profile-info-box h3.hndle:contains(Contact)').parent().find('.table-profile .value');
    var contactAddress = '';
    jQuery( addresses ).each(function( index ) {
        if (index == 0){
            if(jQuery( this ).text() != ""){
                contactAddress = jQuery( this ).text();
            }
        }else{
            if(jQuery( this ).text() != ""){
                contactAddress += ", " + jQuery( this ).text();
            }
        }
    });
    var Getremarks = '';
    jQuery( '.erp-deals-timeline .timeline-content-container .single-item-note .note' ).each(function( indexRemarks ) {
        if (indexRemarks == 0){
            Getremarks = jQuery( this ).children('div').children().html().replace('<!--block-->','');
        }
        else{
            Getremarks += ' | ' + jQuery( this ).children('div').children().html().replace('<!--block-->','');
        }
    });

    dataDeal["title"] = jQuery('.deal-title h1').text();
    ParsePosition = jQuery.parseHTML(jQuery('.step-progressbar .active').attr('tooltip-title'));
    dataDeal["position"] = jQuery(ParsePosition).find('strong').text();
    dataDeal["enquiry_no"] = jQuery('.deal-summery-top .deal-company strong:contains(Deals Id)').parent().children('span').text();
    dataDeal["estimate"] = jQuery('.deal-summery-top .deal-company strong:contains(Estimate Id:)').parent().children('span').text();
    dataDeal["owner"] = jQuery('.deal-owner img').attr("title");
    dataDeal["enquiry_date"] = jQuery('.single-deal-overview td.half-width:contains(Created at)').next().text();
    dataDeal["customer_code"] = getParameterByName('id',jQuery('.deal-summery-top .deal-contact a').attr('href'));
    dataDeal["email"] = jQuery('.profile-info-box h3.hndle:contains(Contact)').parent().find('i.dashicons-email-alt').next('a').text();
    dataDeal["mobile"] = jQuery('.profile-info-box h3.hndle:contains(Contact)').parent().find('i.dashicons-smartphone').next('a').text();
    dataDeal["phone"] = jQuery('.profile-info-box h3.hndle:contains(Contact)').parent().find('i.dashicons-phone').next('a').text();
    dataDeal["company"] = jQuery('.profile-info-box h3.hndle:contains(Company)').parent().find('.profile-summery .summery h3 a').text();
    dataDeal["address"] = contactAddress;
    dataDeal["contact_person"] = jQuery('.profile-info-box h3.hndle:contains(Contact)').parent().find('.profile-summery .summery h3 a').text();
    dataDeal["visit_remarks"] = Getremarks;

    var Competaitors = "";
    jQuery('td.column-competitor-name').each(function(){
        Competaitors += jQuery(this).text() + ", ";
    })
    dataDeal["Competaitors"] = Competaitors;

    //Not Available
    dataDeal["branch"] = '';
    dataDeal["architect"] = '';
    dataDeal["contractor"] = '';
    dataDeal["ProjectContact"] = '';


    //Call From Ajax
    if(dataDeal["estimate"] == "") {
        dataDeal["estimate"] = 0;
    }
        jQuery.ajax({
            url: ajaxurl,
            type : 'post',
            data: {
                action: 'estimateDealDetail',
                contact_id: dataDeal["customer_code"],
                orderId: dataDeal["estimate"]
            },
            success: function (data) {
                RespondReturn['estimateData'] = JSON.parse(data);
                if(dataDeal["estimate"] == 0) {
                    dataDeal["estimate"] = "Not finalized yet.";
                    dataDeal['office_cordinator'] = "Not finalized yet.";
                    dataDeal["items"] = "Not finalized yet.";
                    dataDeal["fitting_address"] = "Not finalized yet.";
                }else{
                    dataDeal["fitting_address"] = RespondReturn['estimateData'].fitting_address;
                    dataDeal["items"] = RespondReturn['estimateData'].products;
                    dataDeal["office_cordinator"] = RespondReturn['estimateData'].field_cordinator;
                }
                dataDeal["branch"] = RespondReturn['estimateData'].branch;
                dataDeal["sales_area"] = RespondReturn['estimateData'].salesZone;
            },
            complete: function () {

                var htmlDeal = "<div class='wrapper-printing'><div class='headerRow InnerRows'><table width='100%' cellspacing='0'><tbody><tr><td rowspan='3' class='logo-print'><img src='" + logo + "'></td><td>" + dataDeal['office_cordinator'] + "</td><td style='width: 75px;'>" + dataDeal['sales_area'] + "</td><td>" + dataDeal['estimate'] + "</td><td>" + dataDeal['enquiry_no'] + "</td><td>&nbsp;</td><td>" + dataDeal['enquiry_date'] + "</td><td style='width: 75px;'>&nbsp;</td><td style='text-align:right;'><i>Staple Business Card Here</i></td></tr><tr class='background-row'><td>Sales Person</td><td>Area</td><td>Estimate No</td><td>Enquiry No</td><td>Job Order No</td><td>Enquiry Date</td><td>Visit Date</td><td style='vertical-align: top; color:#000; background: white;font-size:10px;' rowspan='2'><strong>Assigned Person: </strong>" + dataDeal["Competaitors"] + "</td></tr><tr class='NoBorderRight'><td>" + dataDeal['owner'] + "</td><td>" + dataDeal['branch'] + "</td><td>" + dataDeal['customer_code'] + "</td><td>&nbsp;</td><td>&nbsp;</td><td colspan='2'>&nbsp;</td></tr><tr class='background-row'><td class='logo-slogan'>UAN : 111 BLINDS (111-254-637)</td><td>Office Coordinator</td><td>Branch</td><td>Customer Code</td><td>Payment Receipt No</td><td>Job Completion No</td><td colspan='2' style='width:10%'>Source of Inquiry</td><td class='special-column-title'>" + dataDeal["title"] + " | " + dataDeal["position"] + "</td></tr></tbody></table></div> <div class='InnerRows'> <table width='100%' cellspacing='0'><tbody><tr><td class='checkbox-row'><div> <span class='inside-full-line'>Office Under Renovation <input type='checkbox'></span> <span class='inside-full-line'>Home Under Renovation <input type='checkbox'></span> <span class='inside-full-line'>Office New Construction <input type='checkbox'></span> <span class='inside-full-line'>Home New Construction <input type='checkbox'></span></div></td><td>Email: " + dataDeal['email'] + "</td><td>Mobile No: " + dataDeal['phone'] + "</td><td>Phone No: " + dataDeal['mobile'] + "</td></tr></tbody></table></div> <div class='InnerRows footerRow'><table width='100%' cellspacing='0'><tbody><tr><td>Company Name: " + dataDeal['company'] + "</td><td>Address: " + dataDeal['address'] + "</td></tr><tr><td>Contact Person: " + dataDeal['contact_person'] + "</td><td>Fitting Address: " + dataDeal['fitting_address'] + "</td></tr><tr><td>Architect:</td><td>Contractor:</td></tr><tr><td>Project Contact: </td><td rowspan='2'>Activity Notes: " + dataDeal['visit_remarks'] + "</td></tr><tr class='NoBorderRight'><td>Items: " + dataDeal['items'] + "</td></tr><tr class='FullField'><td colspan='2'>Items Required: </td></tr><tr class='FullField ItemRemarks'><td colspan='2'>Visit Remarks: </td></tr></tbody></table></div></div>";
                var headerPrint = "<title>Print >> Deal:: " + dataDeal["title"] +"</title>";
                headerPrint += "<link rel='stylesheet' type='text/css' href='" + window.location.origin + "/wp-content/plugins/protectors-customization/assets/css/print.css'>";
                headerPrint += "<link rel='stylesheet' media='print' type='text/css' href='" + window.location.origin + "/wp-content/plugins/protectors-customization/assets/css/print.css'>";

                var printDealWindow = window.open('','','scrollbars=yes,resizable=yes,top=50,left=200,width=1060,height=760');

                printDealWindow.document.open();

                printDealWindow.document.write('<html><head>'+headerPrint+'</head><body onload="window.print()">'+htmlDeal+'</body></html>');

                printDealWindow.document.close();

                setTimeout(function(){ printDealWindow.print(); }, 1000);
            },
        });


}

function getParameterByName(name, url = window.location.href) {
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}
