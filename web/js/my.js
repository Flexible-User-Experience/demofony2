'use strict';

angular.module('discussionShowApp', [
        'discussionShowApp.services',
        'ngCookies',
        'ngResource',
        'ngSanitize',
        'ngRoute',
        'uiGmapgoogle-maps',
        'restangular'

    ]).config(['$interpolateProvider', function($interpolateProvider) {
        $interpolateProvider.startSymbol('[[');
        $interpolateProvider.endSymbol(']]');

    }]).config(function(uiGmapGoogleMapApiProvider) {
        uiGmapGoogleMapApiProvider.configure({
//            key: '', // TODO set Google Maps API key
            v: '3.17',
            language: 'es',
            sensor: false,
            libraries: 'drawing,geometry,visualization'
        });
    })
     .constant('CFG', {
        DELAY: 600,
        RANGE_STEPS: 20,
        GMAPS_ZOOM: 14,
        GPS_CENTER_POS: { lat: 41.4926867, lng: 2.3613954}, // Premià de Mar (Barcelona) center
        PROCESS_PARTICIPATION_STATE: {PRESENTATION: 1, DEBATE: 2, CLOSED: 3}
    })
;



'use strict';

angular.module('discussionShowApp').controller('MainCtrl', ['CFG', 'uiGmapGoogleMapApi', '$scope', '$timeout', '$routeParams', '$log', 'Restangular', '$q', 'Security', '$http', function(CFG, uiGmapGoogleMapApi, $scope, $timeout, $routeParams, $log, Restangular, $q, Security, $http) {

    $scope.init = function(discussion, comments, isLogged, username) {
        $scope.discussion = angular.fromJson(discussion);
        $scope.comments = angular.fromJson(comments);
        $scope.is_logged = isLogged;
        $scope.username = username;
        $scope.canVotePromise = Security.canVoteInProcessParticipation($scope.discussion.state, $scope.is_logged);
        $scope.map = { zoom: CFG.GMAPS_ZOOM };
        $scope.map.options = {
            scrollwheel: true,
            draggable: true,
            maxZoom: 15
        };
        $scope.map.control = {};
        $scope.currentPage = 1;
        $scope.comment.update();

        $log.log($scope.discussion);
        $log.log($scope.comments);
        $log.log($scope.comments.count);
        $log.log($scope.pages);
    };

    $scope.vote = function(answer) {
        $scope.canVotePromise.then(function() {
            var url = Routing.generate('api_post_processparticipation_answers_vote', { id: $scope.discussion.id, answer_id: answer.id });
            //substring is to resolve a bug between routing.generate and restangular
            var vote = Restangular.all(url.substring(1));

            if (!answer.user_has_vote_this_proposal_answer) {
                var data = {'comment': null};
                vote.post(data).then(function() {
                    answer.votes_count++;
                    answer.user_has_vote_this_proposal_answer = true;
                    $scope.discussion.user_already_vote = true;
                    $scope.discussion.total_votes_count++;
                });

                return;
            }
            vote.remove().then(function() {
                answer.votes_count--;
                answer.user_has_vote_this_proposal_answer = false;
                $scope.discussion.user_already_vote = false;
                $scope.discussion.total_votes_count--;
            });
        }, function() {
             $scope.showModal.login();
        });
    };

    $scope.comment = {
        like: function(comment, index) {
             $scope.canVotePromise.then(function() {
                 var url = Routing.generate('api_post_processparticipation_comments_like', {
                     id: $scope.discussion.id,
                     comment_id: comment.id
                 });
                 //substring is to resolve a bug between routing.generate and restangular
                 var like = Restangular.all(url.substring(1));
                 if (!comment.user_already_like) {
                     like.post().then(function (result) {
                         $scope.comments.comments[index] = result;
                     });

                     return;
                 }
                 like.remove().then(function (result) {
                     $scope.comments.comments[index] = result;
                 });
             }, function() {
                 $scope.showModal.login();
             });
        },
        unlike: function(comment, index) {
            $scope.canVotePromise.then(function() {
                var url = Routing.generate('api_post_processparticipation_comments_unlike', { id: $scope.discussion.id, comment_id: comment.id });
                //substring is to resolve a bug between routing.generate and restangular
                var like = Restangular.all(url.substring(1));
                if (!comment.user_already_unlike) {
                    like.post().then(function(result) {
                        $scope.comments.comments[index] = result;
                    });

                    return;
                }
                like.remove().then(function(result) {
                    $scope.comments.comments[index] = result;
                });
            }, function() {
                $scope.showModal.login();
            });
        },
        post: function (commentTosend) { // avoid unused function parameter function(commentTosend, parent)

            $scope.canVotePromise.then(function() {
                var url = Routing.generate('api_post_processparticipation_comments', { id: $scope.discussion.id});
                var comment = Restangular.all(url.substring(1));
                comment.post(commentTosend).then(function(result) {
                    result.likes_count = 0;
                    result.unlikes_count = 0;
                    $scope.comments.comments.unshift(result);
                    //$('form').reset();

                });
            }, function() {
                $scope.showModal.login();
            });
        },
        put: function (commentTosend) {
            $scope.canVotePromise.then(function() {
                var url = Routing.generate('api_put_processparticipation_comments', { id: $scope.discussion.id, comment_id: commentTosend.id });
                var comment = Restangular.all(url.substring(1));
                var tosend = {title: commentTosend.title, comment: commentTosend.comment};
                comment.customPUT(tosend).then(function() { // avoid unused function parameter function(result)
                    jQuery('#edit-comment-' + commentTosend.id).addClass('hide');
                });
            }, function() {
                $scope.showModal.login();
            });
        },
        showEditForm: function (id) {
            jQuery('#edit-comment-' + id).removeClass('hide');
        },
        getListLevel1: function (page) {
            $http.get(Routing.generate('api_get_processparticipation_comments', {id: $scope.discussion.id, page: page}, false)).success(function (data) {
                $scope.comments = data ;
                $scope.comment.update();
                $scope.currentPage = page;
            });
        },
        getAnswers: function (comment) {
            $http.get(Routing.generate('api_get_processparticipation_comments_childrens', {id: $scope.discussion.id, comment_id: comment.id}, false)).success(function (data) {
                comment.answers = data;
            });
        },
        update: function () {
            $scope.pages = Math.ceil($scope.comments.count/10);
        }
    };

    $scope.showModal = {
        login: function() {
            if (!$scope.is_logged) {
                jQuery('#login-modal-form').modal({show: true});
            }
        }
    };

    $scope.range = function(n) {
        return new Array(n);
    };

    $scope.getComments = function() { // avoid unused function parameter function(page)
    };

    uiGmapGoogleMapApi.then(function (maps) {
        // promise done
        $log.log('uiGmapGoogleMapApi loaded', maps);
    });

}]);

'use strict';

var services = angular.module('discussionShowApp.services', []);

services.factory('Security', function($q, CFG) {
    return {
        canVoteInProcessParticipation: function(state, is_logged) {
              return $q(function(resolve, reject) {
                  console.log('entra123');
                if (!is_logged) {
                    reject();
                }else if (state === CFG.PROCESS_PARTICIPATION_STATE.DEBATE && is_logged) {
                    resolve();
                }else {

                }
            });
        }
    };
});
