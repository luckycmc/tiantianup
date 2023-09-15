<?php

/**
 * A helper file for Dcat Admin, to provide autocomplete information to your IDE
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author jqh <841324345@qq.com>
 */
namespace Dcat\Admin {
    use Illuminate\Support\Collection;

    /**
     * @property Grid\Column|Collection id
     * @property Grid\Column|Collection active_id
     * @property Grid\Column|Collection type
     * @property Grid\Column|Collection first_reward
     * @property Grid\Column|Collection second_reward
     * @property Grid\Column|Collection created_at
     * @property Grid\Column|Collection updated_at
     * @property Grid\Column|Collection name
     * @property Grid\Column|Collection image
     * @property Grid\Column|Collection object
     * @property Grid\Column|Collection reward
     * @property Grid\Column|Collection introduction
     * @property Grid\Column|Collection start_time
     * @property Grid\Column|Collection end_time
     * @property Grid\Column|Collection status
     * @property Grid\Column|Collection version
     * @property Grid\Column|Collection detail
     * @property Grid\Column|Collection is_enabled
     * @property Grid\Column|Collection parent_id
     * @property Grid\Column|Collection order
     * @property Grid\Column|Collection icon
     * @property Grid\Column|Collection uri
     * @property Grid\Column|Collection extension
     * @property Grid\Column|Collection permission_id
     * @property Grid\Column|Collection menu_id
     * @property Grid\Column|Collection slug
     * @property Grid\Column|Collection http_method
     * @property Grid\Column|Collection http_path
     * @property Grid\Column|Collection role_id
     * @property Grid\Column|Collection user_id
     * @property Grid\Column|Collection value
     * @property Grid\Column|Collection username
     * @property Grid\Column|Collection mobile
     * @property Grid\Column|Collection password
     * @property Grid\Column|Collection email
     * @property Grid\Column|Collection avatar
     * @property Grid\Column|Collection personal_status
     * @property Grid\Column|Collection remember_token
     * @property Grid\Column|Collection last_login_time
     * @property Grid\Column|Collection content
     * @property Grid\Column|Collection user_image
     * @property Grid\Column|Collection teacher_image
     * @property Grid\Column|Collection logo
     * @property Grid\Column|Collection poster
     * @property Grid\Column|Collection service_price
     * @property Grid\Column|Collection amount
     * @property Grid\Column|Collection course_id
     * @property Grid\Column|Collection teacher_id
     * @property Grid\Column|Collection adder_id
     * @property Grid\Column|Collection editer_id
     * @property Grid\Column|Collection consult_time
     * @property Grid\Column|Collection organ_id
     * @property Grid\Column|Collection role
     * @property Grid\Column|Collection grade
     * @property Grid\Column|Collection cover_image
     * @property Grid\Column|Collection method
     * @property Grid\Column|Collection subject
     * @property Grid\Column|Collection count_min
     * @property Grid\Column|Collection count_max
     * @property Grid\Column|Collection class_number
     * @property Grid\Column|Collection class_price
     * @property Grid\Column|Collection duration
     * @property Grid\Column|Collection class_duration
     * @property Grid\Column|Collection class_date
     * @property Grid\Column|Collection class_type
     * @property Grid\Column|Collection base_count
     * @property Grid\Column|Collection base_price
     * @property Grid\Column|Collection improve_count
     * @property Grid\Column|Collection improve_price
     * @property Grid\Column|Collection max_price
     * @property Grid\Column|Collection adder_role
     * @property Grid\Column|Collection class_commission
     * @property Grid\Column|Collection reviewer
     * @property Grid\Column|Collection reason
     * @property Grid\Column|Collection entry_number
     * @property Grid\Column|Collection wechat
     * @property Grid\Column|Collection city
     * @property Grid\Column|Collection tag
     * @property Grid\Column|Collection presale_header_id
     * @property Grid\Column|Collection aftersale_header_id
     * @property Grid\Column|Collection introduce
     * @property Grid\Column|Collection uuid
     * @property Grid\Column|Collection connection
     * @property Grid\Column|Collection queue
     * @property Grid\Column|Collection payload
     * @property Grid\Column|Collection exception
     * @property Grid\Column|Collection failed_at
     * @property Grid\Column|Collection send_platform
     * @property Grid\Column|Collection author
     * @property Grid\Column|Collection url
     * @property Grid\Column|Collection privilege
     * @property Grid\Column|Collection privilege_id
     * @property Grid\Column|Collection update_at
     * @property Grid\Column|Collection nature
     * @property Grid\Column|Collection training_type
     * @property Grid\Column|Collection contact
     * @property Grid\Column|Collection id_card_no
     * @property Grid\Column|Collection province_id
     * @property Grid\Column|Collection city_id
     * @property Grid\Column|Collection district_id
     * @property Grid\Column|Collection address
     * @property Grid\Column|Collection longitude
     * @property Grid\Column|Collection latitude
     * @property Grid\Column|Collection door_image
     * @property Grid\Column|Collection business_license
     * @property Grid\Column|Collection reviewer_id
     * @property Grid\Column|Collection student_id
     * @property Grid\Column|Collection class_time
     * @property Grid\Column|Collection class_price_min
     * @property Grid\Column|Collection class_price_max
     * @property Grid\Column|Collection notes
     * @property Grid\Column|Collection gender
     * @property Grid\Column|Collection school
     * @property Grid\Column|Collection birthday
     * @property Grid\Column|Collection token
     * @property Grid\Column|Collection tokenable_type
     * @property Grid\Column|Collection tokenable_id
     * @property Grid\Column|Collection abilities
     * @property Grid\Column|Collection last_used_at
     * @property Grid\Column|Collection region_name
     * @property Grid\Column|Collection code
     * @property Grid\Column|Collection initial
     * @property Grid\Column|Collection region_type
     * @property Grid\Column|Collection is_last
     * @property Grid\Column|Collection show_platform
     * @property Grid\Column|Collection organization
     * @property Grid\Column|Collection teaching_type
     * @property Grid\Column|Collection id_card_front
     * @property Grid\Column|Collection id_card_backend
     * @property Grid\Column|Collection real_name
     * @property Grid\Column|Collection real_auth_reason
     * @property Grid\Column|Collection picture
     * @property Grid\Column|Collection highest_education
     * @property Grid\Column|Collection education_id
     * @property Grid\Column|Collection graduate_school
     * @property Grid\Column|Collection speciality
     * @property Grid\Column|Collection graduate_cert
     * @property Grid\Column|Collection diploma
     * @property Grid\Column|Collection teacher_cert
     * @property Grid\Column|Collection education_reason
     * @property Grid\Column|Collection teaching_year
     * @property Grid\Column|Collection data_status
     * @property Grid\Column|Collection out_trade_no
     * @property Grid\Column|Collection discount
     * @property Grid\Column|Collection pay_type
     * @property Grid\Column|Collection nickname
     * @property Grid\Column|Collection organ_role_id
     * @property Grid\Column|Collection age
     * @property Grid\Column|Collection total_income
     * @property Grid\Column|Collection withdraw_balance
     * @property Grid\Column|Collection is_real_auth
     * @property Grid\Column|Collection is_education
     * @property Grid\Column|Collection has_teacher_cert
     * @property Grid\Column|Collection is_recommend
     * @property Grid\Column|Collection open_id
     * @property Grid\Column|Collection account
     *
     * @method Grid\Column|Collection id(string $label = null)
     * @method Grid\Column|Collection active_id(string $label = null)
     * @method Grid\Column|Collection type(string $label = null)
     * @method Grid\Column|Collection first_reward(string $label = null)
     * @method Grid\Column|Collection second_reward(string $label = null)
     * @method Grid\Column|Collection created_at(string $label = null)
     * @method Grid\Column|Collection updated_at(string $label = null)
     * @method Grid\Column|Collection name(string $label = null)
     * @method Grid\Column|Collection image(string $label = null)
     * @method Grid\Column|Collection object(string $label = null)
     * @method Grid\Column|Collection reward(string $label = null)
     * @method Grid\Column|Collection introduction(string $label = null)
     * @method Grid\Column|Collection start_time(string $label = null)
     * @method Grid\Column|Collection end_time(string $label = null)
     * @method Grid\Column|Collection status(string $label = null)
     * @method Grid\Column|Collection version(string $label = null)
     * @method Grid\Column|Collection detail(string $label = null)
     * @method Grid\Column|Collection is_enabled(string $label = null)
     * @method Grid\Column|Collection parent_id(string $label = null)
     * @method Grid\Column|Collection order(string $label = null)
     * @method Grid\Column|Collection icon(string $label = null)
     * @method Grid\Column|Collection uri(string $label = null)
     * @method Grid\Column|Collection extension(string $label = null)
     * @method Grid\Column|Collection permission_id(string $label = null)
     * @method Grid\Column|Collection menu_id(string $label = null)
     * @method Grid\Column|Collection slug(string $label = null)
     * @method Grid\Column|Collection http_method(string $label = null)
     * @method Grid\Column|Collection http_path(string $label = null)
     * @method Grid\Column|Collection role_id(string $label = null)
     * @method Grid\Column|Collection user_id(string $label = null)
     * @method Grid\Column|Collection value(string $label = null)
     * @method Grid\Column|Collection username(string $label = null)
     * @method Grid\Column|Collection mobile(string $label = null)
     * @method Grid\Column|Collection password(string $label = null)
     * @method Grid\Column|Collection email(string $label = null)
     * @method Grid\Column|Collection avatar(string $label = null)
     * @method Grid\Column|Collection personal_status(string $label = null)
     * @method Grid\Column|Collection remember_token(string $label = null)
     * @method Grid\Column|Collection last_login_time(string $label = null)
     * @method Grid\Column|Collection content(string $label = null)
     * @method Grid\Column|Collection user_image(string $label = null)
     * @method Grid\Column|Collection teacher_image(string $label = null)
     * @method Grid\Column|Collection logo(string $label = null)
     * @method Grid\Column|Collection poster(string $label = null)
     * @method Grid\Column|Collection service_price(string $label = null)
     * @method Grid\Column|Collection amount(string $label = null)
     * @method Grid\Column|Collection course_id(string $label = null)
     * @method Grid\Column|Collection teacher_id(string $label = null)
     * @method Grid\Column|Collection adder_id(string $label = null)
     * @method Grid\Column|Collection editer_id(string $label = null)
     * @method Grid\Column|Collection consult_time(string $label = null)
     * @method Grid\Column|Collection organ_id(string $label = null)
     * @method Grid\Column|Collection role(string $label = null)
     * @method Grid\Column|Collection grade(string $label = null)
     * @method Grid\Column|Collection cover_image(string $label = null)
     * @method Grid\Column|Collection method(string $label = null)
     * @method Grid\Column|Collection subject(string $label = null)
     * @method Grid\Column|Collection count_min(string $label = null)
     * @method Grid\Column|Collection count_max(string $label = null)
     * @method Grid\Column|Collection class_number(string $label = null)
     * @method Grid\Column|Collection class_price(string $label = null)
     * @method Grid\Column|Collection duration(string $label = null)
     * @method Grid\Column|Collection class_duration(string $label = null)
     * @method Grid\Column|Collection class_date(string $label = null)
     * @method Grid\Column|Collection class_type(string $label = null)
     * @method Grid\Column|Collection base_count(string $label = null)
     * @method Grid\Column|Collection base_price(string $label = null)
     * @method Grid\Column|Collection improve_count(string $label = null)
     * @method Grid\Column|Collection improve_price(string $label = null)
     * @method Grid\Column|Collection max_price(string $label = null)
     * @method Grid\Column|Collection adder_role(string $label = null)
     * @method Grid\Column|Collection class_commission(string $label = null)
     * @method Grid\Column|Collection reviewer(string $label = null)
     * @method Grid\Column|Collection reason(string $label = null)
     * @method Grid\Column|Collection entry_number(string $label = null)
     * @method Grid\Column|Collection wechat(string $label = null)
     * @method Grid\Column|Collection city(string $label = null)
     * @method Grid\Column|Collection tag(string $label = null)
     * @method Grid\Column|Collection presale_header_id(string $label = null)
     * @method Grid\Column|Collection aftersale_header_id(string $label = null)
     * @method Grid\Column|Collection introduce(string $label = null)
     * @method Grid\Column|Collection uuid(string $label = null)
     * @method Grid\Column|Collection connection(string $label = null)
     * @method Grid\Column|Collection queue(string $label = null)
     * @method Grid\Column|Collection payload(string $label = null)
     * @method Grid\Column|Collection exception(string $label = null)
     * @method Grid\Column|Collection failed_at(string $label = null)
     * @method Grid\Column|Collection send_platform(string $label = null)
     * @method Grid\Column|Collection author(string $label = null)
     * @method Grid\Column|Collection url(string $label = null)
     * @method Grid\Column|Collection privilege(string $label = null)
     * @method Grid\Column|Collection privilege_id(string $label = null)
     * @method Grid\Column|Collection update_at(string $label = null)
     * @method Grid\Column|Collection nature(string $label = null)
     * @method Grid\Column|Collection training_type(string $label = null)
     * @method Grid\Column|Collection contact(string $label = null)
     * @method Grid\Column|Collection id_card_no(string $label = null)
     * @method Grid\Column|Collection province_id(string $label = null)
     * @method Grid\Column|Collection city_id(string $label = null)
     * @method Grid\Column|Collection district_id(string $label = null)
     * @method Grid\Column|Collection address(string $label = null)
     * @method Grid\Column|Collection longitude(string $label = null)
     * @method Grid\Column|Collection latitude(string $label = null)
     * @method Grid\Column|Collection door_image(string $label = null)
     * @method Grid\Column|Collection business_license(string $label = null)
     * @method Grid\Column|Collection reviewer_id(string $label = null)
     * @method Grid\Column|Collection student_id(string $label = null)
     * @method Grid\Column|Collection class_time(string $label = null)
     * @method Grid\Column|Collection class_price_min(string $label = null)
     * @method Grid\Column|Collection class_price_max(string $label = null)
     * @method Grid\Column|Collection notes(string $label = null)
     * @method Grid\Column|Collection gender(string $label = null)
     * @method Grid\Column|Collection school(string $label = null)
     * @method Grid\Column|Collection birthday(string $label = null)
     * @method Grid\Column|Collection token(string $label = null)
     * @method Grid\Column|Collection tokenable_type(string $label = null)
     * @method Grid\Column|Collection tokenable_id(string $label = null)
     * @method Grid\Column|Collection abilities(string $label = null)
     * @method Grid\Column|Collection last_used_at(string $label = null)
     * @method Grid\Column|Collection region_name(string $label = null)
     * @method Grid\Column|Collection code(string $label = null)
     * @method Grid\Column|Collection initial(string $label = null)
     * @method Grid\Column|Collection region_type(string $label = null)
     * @method Grid\Column|Collection is_last(string $label = null)
     * @method Grid\Column|Collection show_platform(string $label = null)
     * @method Grid\Column|Collection organization(string $label = null)
     * @method Grid\Column|Collection teaching_type(string $label = null)
     * @method Grid\Column|Collection id_card_front(string $label = null)
     * @method Grid\Column|Collection id_card_backend(string $label = null)
     * @method Grid\Column|Collection real_name(string $label = null)
     * @method Grid\Column|Collection real_auth_reason(string $label = null)
     * @method Grid\Column|Collection picture(string $label = null)
     * @method Grid\Column|Collection highest_education(string $label = null)
     * @method Grid\Column|Collection education_id(string $label = null)
     * @method Grid\Column|Collection graduate_school(string $label = null)
     * @method Grid\Column|Collection speciality(string $label = null)
     * @method Grid\Column|Collection graduate_cert(string $label = null)
     * @method Grid\Column|Collection diploma(string $label = null)
     * @method Grid\Column|Collection teacher_cert(string $label = null)
     * @method Grid\Column|Collection education_reason(string $label = null)
     * @method Grid\Column|Collection teaching_year(string $label = null)
     * @method Grid\Column|Collection data_status(string $label = null)
     * @method Grid\Column|Collection out_trade_no(string $label = null)
     * @method Grid\Column|Collection discount(string $label = null)
     * @method Grid\Column|Collection pay_type(string $label = null)
     * @method Grid\Column|Collection nickname(string $label = null)
     * @method Grid\Column|Collection organ_role_id(string $label = null)
     * @method Grid\Column|Collection age(string $label = null)
     * @method Grid\Column|Collection total_income(string $label = null)
     * @method Grid\Column|Collection withdraw_balance(string $label = null)
     * @method Grid\Column|Collection is_real_auth(string $label = null)
     * @method Grid\Column|Collection is_education(string $label = null)
     * @method Grid\Column|Collection has_teacher_cert(string $label = null)
     * @method Grid\Column|Collection is_recommend(string $label = null)
     * @method Grid\Column|Collection open_id(string $label = null)
     * @method Grid\Column|Collection account(string $label = null)
     */
    class Grid {}

    class MiniGrid extends Grid {}

    /**
     * @property Show\Field|Collection id
     * @property Show\Field|Collection active_id
     * @property Show\Field|Collection type
     * @property Show\Field|Collection first_reward
     * @property Show\Field|Collection second_reward
     * @property Show\Field|Collection created_at
     * @property Show\Field|Collection updated_at
     * @property Show\Field|Collection name
     * @property Show\Field|Collection image
     * @property Show\Field|Collection object
     * @property Show\Field|Collection reward
     * @property Show\Field|Collection introduction
     * @property Show\Field|Collection start_time
     * @property Show\Field|Collection end_time
     * @property Show\Field|Collection status
     * @property Show\Field|Collection version
     * @property Show\Field|Collection detail
     * @property Show\Field|Collection is_enabled
     * @property Show\Field|Collection parent_id
     * @property Show\Field|Collection order
     * @property Show\Field|Collection icon
     * @property Show\Field|Collection uri
     * @property Show\Field|Collection extension
     * @property Show\Field|Collection permission_id
     * @property Show\Field|Collection menu_id
     * @property Show\Field|Collection slug
     * @property Show\Field|Collection http_method
     * @property Show\Field|Collection http_path
     * @property Show\Field|Collection role_id
     * @property Show\Field|Collection user_id
     * @property Show\Field|Collection value
     * @property Show\Field|Collection username
     * @property Show\Field|Collection mobile
     * @property Show\Field|Collection password
     * @property Show\Field|Collection email
     * @property Show\Field|Collection avatar
     * @property Show\Field|Collection personal_status
     * @property Show\Field|Collection remember_token
     * @property Show\Field|Collection last_login_time
     * @property Show\Field|Collection content
     * @property Show\Field|Collection user_image
     * @property Show\Field|Collection teacher_image
     * @property Show\Field|Collection logo
     * @property Show\Field|Collection poster
     * @property Show\Field|Collection service_price
     * @property Show\Field|Collection amount
     * @property Show\Field|Collection course_id
     * @property Show\Field|Collection teacher_id
     * @property Show\Field|Collection adder_id
     * @property Show\Field|Collection editer_id
     * @property Show\Field|Collection consult_time
     * @property Show\Field|Collection organ_id
     * @property Show\Field|Collection role
     * @property Show\Field|Collection grade
     * @property Show\Field|Collection cover_image
     * @property Show\Field|Collection method
     * @property Show\Field|Collection subject
     * @property Show\Field|Collection count_min
     * @property Show\Field|Collection count_max
     * @property Show\Field|Collection class_number
     * @property Show\Field|Collection class_price
     * @property Show\Field|Collection duration
     * @property Show\Field|Collection class_duration
     * @property Show\Field|Collection class_date
     * @property Show\Field|Collection class_type
     * @property Show\Field|Collection base_count
     * @property Show\Field|Collection base_price
     * @property Show\Field|Collection improve_count
     * @property Show\Field|Collection improve_price
     * @property Show\Field|Collection max_price
     * @property Show\Field|Collection adder_role
     * @property Show\Field|Collection class_commission
     * @property Show\Field|Collection reviewer
     * @property Show\Field|Collection reason
     * @property Show\Field|Collection entry_number
     * @property Show\Field|Collection wechat
     * @property Show\Field|Collection city
     * @property Show\Field|Collection tag
     * @property Show\Field|Collection presale_header_id
     * @property Show\Field|Collection aftersale_header_id
     * @property Show\Field|Collection introduce
     * @property Show\Field|Collection uuid
     * @property Show\Field|Collection connection
     * @property Show\Field|Collection queue
     * @property Show\Field|Collection payload
     * @property Show\Field|Collection exception
     * @property Show\Field|Collection failed_at
     * @property Show\Field|Collection send_platform
     * @property Show\Field|Collection author
     * @property Show\Field|Collection url
     * @property Show\Field|Collection privilege
     * @property Show\Field|Collection privilege_id
     * @property Show\Field|Collection update_at
     * @property Show\Field|Collection nature
     * @property Show\Field|Collection training_type
     * @property Show\Field|Collection contact
     * @property Show\Field|Collection id_card_no
     * @property Show\Field|Collection province_id
     * @property Show\Field|Collection city_id
     * @property Show\Field|Collection district_id
     * @property Show\Field|Collection address
     * @property Show\Field|Collection longitude
     * @property Show\Field|Collection latitude
     * @property Show\Field|Collection door_image
     * @property Show\Field|Collection business_license
     * @property Show\Field|Collection reviewer_id
     * @property Show\Field|Collection student_id
     * @property Show\Field|Collection class_time
     * @property Show\Field|Collection class_price_min
     * @property Show\Field|Collection class_price_max
     * @property Show\Field|Collection notes
     * @property Show\Field|Collection gender
     * @property Show\Field|Collection school
     * @property Show\Field|Collection birthday
     * @property Show\Field|Collection token
     * @property Show\Field|Collection tokenable_type
     * @property Show\Field|Collection tokenable_id
     * @property Show\Field|Collection abilities
     * @property Show\Field|Collection last_used_at
     * @property Show\Field|Collection region_name
     * @property Show\Field|Collection code
     * @property Show\Field|Collection initial
     * @property Show\Field|Collection region_type
     * @property Show\Field|Collection is_last
     * @property Show\Field|Collection show_platform
     * @property Show\Field|Collection organization
     * @property Show\Field|Collection teaching_type
     * @property Show\Field|Collection id_card_front
     * @property Show\Field|Collection id_card_backend
     * @property Show\Field|Collection real_name
     * @property Show\Field|Collection real_auth_reason
     * @property Show\Field|Collection picture
     * @property Show\Field|Collection highest_education
     * @property Show\Field|Collection education_id
     * @property Show\Field|Collection graduate_school
     * @property Show\Field|Collection speciality
     * @property Show\Field|Collection graduate_cert
     * @property Show\Field|Collection diploma
     * @property Show\Field|Collection teacher_cert
     * @property Show\Field|Collection education_reason
     * @property Show\Field|Collection teaching_year
     * @property Show\Field|Collection data_status
     * @property Show\Field|Collection out_trade_no
     * @property Show\Field|Collection discount
     * @property Show\Field|Collection pay_type
     * @property Show\Field|Collection nickname
     * @property Show\Field|Collection organ_role_id
     * @property Show\Field|Collection age
     * @property Show\Field|Collection total_income
     * @property Show\Field|Collection withdraw_balance
     * @property Show\Field|Collection is_real_auth
     * @property Show\Field|Collection is_education
     * @property Show\Field|Collection has_teacher_cert
     * @property Show\Field|Collection is_recommend
     * @property Show\Field|Collection open_id
     * @property Show\Field|Collection account
     *
     * @method Show\Field|Collection id(string $label = null)
     * @method Show\Field|Collection active_id(string $label = null)
     * @method Show\Field|Collection type(string $label = null)
     * @method Show\Field|Collection first_reward(string $label = null)
     * @method Show\Field|Collection second_reward(string $label = null)
     * @method Show\Field|Collection created_at(string $label = null)
     * @method Show\Field|Collection updated_at(string $label = null)
     * @method Show\Field|Collection name(string $label = null)
     * @method Show\Field|Collection image(string $label = null)
     * @method Show\Field|Collection object(string $label = null)
     * @method Show\Field|Collection reward(string $label = null)
     * @method Show\Field|Collection introduction(string $label = null)
     * @method Show\Field|Collection start_time(string $label = null)
     * @method Show\Field|Collection end_time(string $label = null)
     * @method Show\Field|Collection status(string $label = null)
     * @method Show\Field|Collection version(string $label = null)
     * @method Show\Field|Collection detail(string $label = null)
     * @method Show\Field|Collection is_enabled(string $label = null)
     * @method Show\Field|Collection parent_id(string $label = null)
     * @method Show\Field|Collection order(string $label = null)
     * @method Show\Field|Collection icon(string $label = null)
     * @method Show\Field|Collection uri(string $label = null)
     * @method Show\Field|Collection extension(string $label = null)
     * @method Show\Field|Collection permission_id(string $label = null)
     * @method Show\Field|Collection menu_id(string $label = null)
     * @method Show\Field|Collection slug(string $label = null)
     * @method Show\Field|Collection http_method(string $label = null)
     * @method Show\Field|Collection http_path(string $label = null)
     * @method Show\Field|Collection role_id(string $label = null)
     * @method Show\Field|Collection user_id(string $label = null)
     * @method Show\Field|Collection value(string $label = null)
     * @method Show\Field|Collection username(string $label = null)
     * @method Show\Field|Collection mobile(string $label = null)
     * @method Show\Field|Collection password(string $label = null)
     * @method Show\Field|Collection email(string $label = null)
     * @method Show\Field|Collection avatar(string $label = null)
     * @method Show\Field|Collection personal_status(string $label = null)
     * @method Show\Field|Collection remember_token(string $label = null)
     * @method Show\Field|Collection last_login_time(string $label = null)
     * @method Show\Field|Collection content(string $label = null)
     * @method Show\Field|Collection user_image(string $label = null)
     * @method Show\Field|Collection teacher_image(string $label = null)
     * @method Show\Field|Collection logo(string $label = null)
     * @method Show\Field|Collection poster(string $label = null)
     * @method Show\Field|Collection service_price(string $label = null)
     * @method Show\Field|Collection amount(string $label = null)
     * @method Show\Field|Collection course_id(string $label = null)
     * @method Show\Field|Collection teacher_id(string $label = null)
     * @method Show\Field|Collection adder_id(string $label = null)
     * @method Show\Field|Collection editer_id(string $label = null)
     * @method Show\Field|Collection consult_time(string $label = null)
     * @method Show\Field|Collection organ_id(string $label = null)
     * @method Show\Field|Collection role(string $label = null)
     * @method Show\Field|Collection grade(string $label = null)
     * @method Show\Field|Collection cover_image(string $label = null)
     * @method Show\Field|Collection method(string $label = null)
     * @method Show\Field|Collection subject(string $label = null)
     * @method Show\Field|Collection count_min(string $label = null)
     * @method Show\Field|Collection count_max(string $label = null)
     * @method Show\Field|Collection class_number(string $label = null)
     * @method Show\Field|Collection class_price(string $label = null)
     * @method Show\Field|Collection duration(string $label = null)
     * @method Show\Field|Collection class_duration(string $label = null)
     * @method Show\Field|Collection class_date(string $label = null)
     * @method Show\Field|Collection class_type(string $label = null)
     * @method Show\Field|Collection base_count(string $label = null)
     * @method Show\Field|Collection base_price(string $label = null)
     * @method Show\Field|Collection improve_count(string $label = null)
     * @method Show\Field|Collection improve_price(string $label = null)
     * @method Show\Field|Collection max_price(string $label = null)
     * @method Show\Field|Collection adder_role(string $label = null)
     * @method Show\Field|Collection class_commission(string $label = null)
     * @method Show\Field|Collection reviewer(string $label = null)
     * @method Show\Field|Collection reason(string $label = null)
     * @method Show\Field|Collection entry_number(string $label = null)
     * @method Show\Field|Collection wechat(string $label = null)
     * @method Show\Field|Collection city(string $label = null)
     * @method Show\Field|Collection tag(string $label = null)
     * @method Show\Field|Collection presale_header_id(string $label = null)
     * @method Show\Field|Collection aftersale_header_id(string $label = null)
     * @method Show\Field|Collection introduce(string $label = null)
     * @method Show\Field|Collection uuid(string $label = null)
     * @method Show\Field|Collection connection(string $label = null)
     * @method Show\Field|Collection queue(string $label = null)
     * @method Show\Field|Collection payload(string $label = null)
     * @method Show\Field|Collection exception(string $label = null)
     * @method Show\Field|Collection failed_at(string $label = null)
     * @method Show\Field|Collection send_platform(string $label = null)
     * @method Show\Field|Collection author(string $label = null)
     * @method Show\Field|Collection url(string $label = null)
     * @method Show\Field|Collection privilege(string $label = null)
     * @method Show\Field|Collection privilege_id(string $label = null)
     * @method Show\Field|Collection update_at(string $label = null)
     * @method Show\Field|Collection nature(string $label = null)
     * @method Show\Field|Collection training_type(string $label = null)
     * @method Show\Field|Collection contact(string $label = null)
     * @method Show\Field|Collection id_card_no(string $label = null)
     * @method Show\Field|Collection province_id(string $label = null)
     * @method Show\Field|Collection city_id(string $label = null)
     * @method Show\Field|Collection district_id(string $label = null)
     * @method Show\Field|Collection address(string $label = null)
     * @method Show\Field|Collection longitude(string $label = null)
     * @method Show\Field|Collection latitude(string $label = null)
     * @method Show\Field|Collection door_image(string $label = null)
     * @method Show\Field|Collection business_license(string $label = null)
     * @method Show\Field|Collection reviewer_id(string $label = null)
     * @method Show\Field|Collection student_id(string $label = null)
     * @method Show\Field|Collection class_time(string $label = null)
     * @method Show\Field|Collection class_price_min(string $label = null)
     * @method Show\Field|Collection class_price_max(string $label = null)
     * @method Show\Field|Collection notes(string $label = null)
     * @method Show\Field|Collection gender(string $label = null)
     * @method Show\Field|Collection school(string $label = null)
     * @method Show\Field|Collection birthday(string $label = null)
     * @method Show\Field|Collection token(string $label = null)
     * @method Show\Field|Collection tokenable_type(string $label = null)
     * @method Show\Field|Collection tokenable_id(string $label = null)
     * @method Show\Field|Collection abilities(string $label = null)
     * @method Show\Field|Collection last_used_at(string $label = null)
     * @method Show\Field|Collection region_name(string $label = null)
     * @method Show\Field|Collection code(string $label = null)
     * @method Show\Field|Collection initial(string $label = null)
     * @method Show\Field|Collection region_type(string $label = null)
     * @method Show\Field|Collection is_last(string $label = null)
     * @method Show\Field|Collection show_platform(string $label = null)
     * @method Show\Field|Collection organization(string $label = null)
     * @method Show\Field|Collection teaching_type(string $label = null)
     * @method Show\Field|Collection id_card_front(string $label = null)
     * @method Show\Field|Collection id_card_backend(string $label = null)
     * @method Show\Field|Collection real_name(string $label = null)
     * @method Show\Field|Collection real_auth_reason(string $label = null)
     * @method Show\Field|Collection picture(string $label = null)
     * @method Show\Field|Collection highest_education(string $label = null)
     * @method Show\Field|Collection education_id(string $label = null)
     * @method Show\Field|Collection graduate_school(string $label = null)
     * @method Show\Field|Collection speciality(string $label = null)
     * @method Show\Field|Collection graduate_cert(string $label = null)
     * @method Show\Field|Collection diploma(string $label = null)
     * @method Show\Field|Collection teacher_cert(string $label = null)
     * @method Show\Field|Collection education_reason(string $label = null)
     * @method Show\Field|Collection teaching_year(string $label = null)
     * @method Show\Field|Collection data_status(string $label = null)
     * @method Show\Field|Collection out_trade_no(string $label = null)
     * @method Show\Field|Collection discount(string $label = null)
     * @method Show\Field|Collection pay_type(string $label = null)
     * @method Show\Field|Collection nickname(string $label = null)
     * @method Show\Field|Collection organ_role_id(string $label = null)
     * @method Show\Field|Collection age(string $label = null)
     * @method Show\Field|Collection total_income(string $label = null)
     * @method Show\Field|Collection withdraw_balance(string $label = null)
     * @method Show\Field|Collection is_real_auth(string $label = null)
     * @method Show\Field|Collection is_education(string $label = null)
     * @method Show\Field|Collection has_teacher_cert(string $label = null)
     * @method Show\Field|Collection is_recommend(string $label = null)
     * @method Show\Field|Collection open_id(string $label = null)
     * @method Show\Field|Collection account(string $label = null)
     */
    class Show {}

    /**
     
     */
    class Form {}

}

namespace Dcat\Admin\Grid {
    /**
     
     */
    class Column {}

    /**
     
     */
    class Filter {}
}

namespace Dcat\Admin\Show {
    /**
     
     */
    class Field {}
}
