var UserProfile = function (
    ratingUserUrl, averageRankingUser, messageRatingYourSelf, btnClose, urlFollowUser,
    btnFollow, btnUnFollow
    ) {
    this.ratingUserUrl = ratingUserUrl;
    this.averageRankingUser = averageRankingUser;
    this.messageRatingYourSelf = messageRatingYourSelf;
    this.btnClose = btnClose;
    this.urlFollowUser = urlFollowUser;
    this.btnFollow = btnFollow;
    this.btnUnFollow = btnUnFollow;
};

UserProfile.prototype = {
    init: function () {
        var _self = this;
        _self.initStarUser();
        _self.ratingUser();
        _self.notifyRatingUser();
        _self.followOrUnFollowUser();
    },

    ratingUser: function () {
        var _self = this;
        $('#allow-rating-user').on('rating.change', function (event, value) {
            var targetId = $('#target_id').val();
            var token = $('.hide').data('token');
            $.ajax({
                type: "POST",
                url: _self.ratingUserUrl,
                data: {
                    'value': value,
                    'targetId': targetId,
                    '_token': token
                },
                success: function (data) {
                    if (data) {
                        $('#allow-rating-user').rating('update', data.average);
                        $('.reviews-num-user').html(data.amount);
                    }
                }
            });
        });
    },

    notifyRatingUser: function () {
        var _self = this;
        $('#not-allow-rating-user').on('rating.change', function () {
            BootstrapDialog.show({
                title: '',
                message: _self.messageRatingYourSelf,
                buttons: [{
                    label: _self.btnClose,
                    action: function (dialog) {
                        dialog.close();
                        _self.initStarUser();
                    }
                }]
            });
        });
    },

    initStarUser: function () {
        var _self = this;
        $('#allow-rating-user').rating('update', _self.averageRankingUser);
        $('#not-allow-rating-user').rating('update', _self.averageRankingUser);
    },

    followOrUnFollowUser: function () {
        var _self = this;

        $("#follow").click(function(e) {
            e.preventDefault();
            var divChangeAmount = $(this).parent();
            var userId = divChangeAmount.data('userId');
            var token = $('.hide').data('token');

            $.ajax({
                type: "POST",
                url: _self.urlFollowUser,
                data: {
                    target_id: userId,
                    _token: token
                },
                success: function(data)
                {
                    if (data.result.status) {
                        $("#follow").val(_self.btnUnFollow);
                        $("#follow").removeClass('btn-success');
                        $("#follow").addClass('btn-danger');
                    } else {
                        $("#follow").val(_self.btnFollow);
                        $("#follow").removeClass('btn-danger');
                        $("#follow").addClass('btn-success');
                    }
                }
            });
        });
    }
};