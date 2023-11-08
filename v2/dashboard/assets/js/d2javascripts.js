$(document).ready(function () {
    $('.js-example-basic-multiple').select2({
        dropdownAutoWidth: false,
        multiple: true,
        placeholder: "  Select Team Members",
        allowClear: true
    });
    $('.js-example-basic-multiple').on('select2:close', function () {
        const $select = $(this);
        const numSelected = $select.select2('data').length;

        $select.next('span.select2').find('ul').html(function () {
            return `<li class="class="select2-selection__choice">${numSelected} selected</li>`;
        })
    });

    $(document).on("click", ".select2-results__group", function () {

        var groupName = $(this).html()
        var options = $('#salesman option');

        $.each(options, function (key, value) {

            if ($(value)[0].parentElement.label.indexOf(groupName) >= 0) {
                $(value).prop("selected", "selected");
            }

        });

        $("#salesman").trigger("change");
        $("#salesman").select2('close');

    });

    $('#submit').click(function (e) {
        e.preventDefault()
        var salesman = $('#salesman').val();
        var from = $('#from').val();
        var to = $('#to').val();

        if (salesman != "") {
            $('.ajax-loader').css("visibility", "visible");
            $.ajax({
                type: 'POST',
                url: '../V2/dashboard/badges/update-badge.php',
                data: { salesman: salesman, from: from, to: to },
                success: function (response) {

                    // alert(response);
                    var result = $.parseJSON(response);
                    var pdcCount = (result.pdcCount).toLocaleString(
                        undefined,
                    );
                    $('#pdcCount').html(pdcCount);

                    var salestarget = (result.salestarget).toLocaleString(
                        undefined,
                    );
                    $('#salestarget').html(salestarget);

                    var outstanding = (result.outstanding).toLocaleString(
                        undefined,
                    );
                    $('#outstanding').html(outstanding);

                    var totalCart = (result.totalCart).toLocaleString(
                        undefined,
                    );

                    $('.totalCart').html(totalCart);

                    $('.ajax-loader').css("visibility", "hidden");
                }
            });
        }
        else { alert("Please Select Salesman."); }
    });

    $('#submit1').click(function (e) {
        e.preventDefault();
        var salesman = $('#salesman').val();
        var from = $('#from').val();
        var to = $('#to').val();
        startYear = from.substring(0, 4);
        endYear = to.substring(0, 4);
        startMonth = from.substring(7, 5);
        endMonth = to.substring(7, 5);
        if (salesman != "" && from != "" && to != "") {
            $('.ajax-loader').css("visibility", "visible");
            $.ajax({
                type: 'POST',
                url: '../V2/dashboard/badges/update-badge.php',
                data: {
                    salesman: salesman, from: from, to: to, startYear: startYear,
                    startMonth: startMonth, endMonth: endMonth
                },
                success: function (response) {
                    // alert(response);
                    var result = $.parseJSON(response);

                    var targetAcheived = (result.acheivedTarget).toLocaleString(
                        undefined,
                    );
                    $('#targetAcheived').html(targetAcheived);

                    var actualTarget = (result.target).toLocaleString(
                        undefined,
                    );
                    $('#actualTarget').html(actualTarget);

                    $('#acheiveRatio').html(result.acheiveRatio);


                    var salescaseCount = (result.salescaseCount).toLocaleString(
                        undefined,
                    );
                    $('#salescaseCount').html(salescaseCount);
                    $('#salescaseCountSR').html(result.salescaseCountSR);
                    $('#salescaseCountMT').html(result.salescaseCountMT);

                    var quototal = (result.quototal).toLocaleString(
                        undefined,
                    );
                    $('#quototal').html(quototal);

                    var quotationCount = (result.quotationCount).toLocaleString(
                        undefined,
                    );
                    $('#quotationCount').html(quotationCount);
                    $('#quotationCountSR').html(result.quotationCountSR);
                    $('#quotationCountMT').html(result.quotationCountMT);

                   
                    var octotal = (result.octotal).toLocaleString(
                        undefined,
                    );
                    $('#octotal').html(octotal);
                    

                    var ocCount = (result.ocCount).toLocaleString(
                        undefined,
                    );
                    $('#ocCount').html(ocCount);
                    $('#ocCountSR').html(result.ocCountSR);
                    $('#ocCountMT').html(result.ocCountMT);

                    
                    var dctotal = (result.dctotal).toLocaleString(
                        undefined,
                    );
                    $('#dctotal').html(dctotal);

                    var dcCount = (result.dcCount).toLocaleString(
                        undefined,
                    );
                    $('#dcCount').html(dcCount);
                    $('#dcCountSR').html(result.dcCountSR);
                    $('#dcCountMT').html(result.dcCountMT);


                    var totalScore = (result.totalScore).toLocaleString(
                        undefined,
                    );
                    $('#totalScore').html(totalScore);

                    var pdcCount = (result.pdcCount).toLocaleString(
                        undefined,
                    );
                    $('#pdcCount').html(pdcCount);

                    var salestarget = (result.salestarget).toLocaleString(
                        undefined,
                    );
                    $('#salestarget').html(salestarget);

                    var outstanding = (result.outstanding).toLocaleString(
                        undefined,
                    );
                    $('#outstanding').html(outstanding);

                    var totalCart = (result.totalCart).toLocaleString(
                        undefined,
                    );

                    $('#totalCart').html(totalCart);
                    $('.ajax-loader').css("visibility", "hidden");
                }
            });
        }
        else { alert("Please Enter Both Start and End dates Also Salesman."); }
    });

    $('#salescase').click(function () {
        $('#salescaseDiv').show();
        $('#salescaseSRDiv').hide();
        $('#salescaseMTDiv').hide();
    });
    $('#salescaseSR').click(function () {
        $('#salescaseDiv').hide();
        $('#salescaseSRDiv').show();
        $('#salescaseMTDiv').hide();
        $(this).click();

    });
    $('#salescaseMT').click(function () {
        $('#salescaseDiv').hide();
        $('#salescaseSRDiv').hide();
        $('#salescaseMTDiv').show();
    });

    $('#quotation').click(function () {
        $('#quotationDiv').show();
        $('#quotationSRDiv').hide();
        $('#quotationMTDiv').hide();
    });
    $('#quotationSR').click(function () {
        $('#quotationDiv').hide();
        $('#quotationSRDiv').show();
        $('#quotationMTDiv').hide();

    });
    $('#quotationMT').click(function () {
        $('#quotationDiv').hide();
        $('#quotationSRDiv').hide();
        $('#quotationMTDiv').show();
    });

    $('#oc').click(function () {
        $('#ocDiv').show();
        $('#ocSRDiv').hide();
        $('#ocMTDiv').hide();
    });
    $('#ocSR').click(function () {
        $('#ocDiv').hide();
        $('#ocSRDiv').show();
        $('#ocMTDiv').hide();

    });
    $('#ocMT').click(function () {
        $('#ocDiv').hide();
        $('#ocSRDiv').hide();
        $('#ocMTDiv').show();
    });

    $('#dc').click(function () {
        $('#dcDiv').show();
        $('#dcSRDiv').hide();
        $('#dcMTDiv').hide();
    });
    $('#dcSR').click(function () {
        $('#dcDiv').hide();
        $('#dcSRDiv').show();
        $('#dcMTDiv').hide();

    });
    $('#dcMT').click(function () {
        $('#dcDiv').hide();
        $('#dcSRDiv').hide();
        $('#dcMTDiv').show();
    });


});