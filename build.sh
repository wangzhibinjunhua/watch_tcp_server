#!/bin/bash

function get_file_name()
{
    local l_file_name=`echo "$1"|sed -n 's#.*/\([^/][^/]*[/]*\)$#\1#p'`
    echo "$l_file_name"
}

function get_dir_name_in_this_dir()
{
    local l_this_dir_path=""
    local l_dirs_name=""
    local l_name_ret=""
    local l_name=""
    local tmp=""

    if [ "$#" -eq 0 ]; then
		echo "!!error!! you must get one parameter">>"$L_HANDER_LOG_FILE"
		return 1
    fi

    l_this_dir_path="$1"

    if ! [ -d "$l_this_dir_path" ]; then
		echo "!!error!! $l_this_dir_path is't not exist" #>>"$L_HANDER_LOG_FILE"
		return 1
    fi
    l_dirs_name=$(find "$l_this_dir_path" -maxdepth 1 -type d)
    l_dirs_name=$(echo "$l_dirs_name"|sort |sed 'N;s#\n# #g')

    for l_name in $l_dirs_name
    do
		l_name=$(get_file_name "$l_name")
	 	tmp="$(get_file_name "$l_this_dir_path")"

    #echo "1=$( echo \"$l_name\"|sed 's#[/]*$##')"
		if [ $( echo \"$l_name\"|sed 's#[/]*$##') == $(echo \"$tmp\"|sed 's#[/]*$##') ]; then
	    	continue 1
		fi
		if [ $( echo \"$l_name\"|sed 's#[/]*$##') == "\"default\"" ]; then
	    	continue 1
		fi
		if [ $( echo \"$l_name\"|sed 's#[/]*$##') == "\"tools\"" ]; then
	    	continue 1
		fi
		l_name_ret="$l_name_ret$l_name "
    done

    echo "$l_name_ret"
}








#echo -e '\033[0;31;1m'
#echo '---------Project List-------------'
#echo -e '\033[0m'
l_menu_name=0
l_menu_id=0
l_into_dir="Applications"
l_projects_name=($(get_dir_name_in_this_dir "$l_into_dir"))
echo -e '\033[0;31;1m'
echo '---------Project Status---------------------------'
echo -e '\033[0m'
for l_menu_name in ${l_projects_name[@]}
do

    echo -e "\t$l_menu_id.$l_menu_name"
    echo status:
    php start_$l_menu_name.php status
    echo
    echo -e '\033[0;31;1m'
    echo '----------------------------------------------------------------------'
    echo -e '\033[0m'
  	let l_menu_id=l_menu_id+1
done
read l_select_id

PRJ_NAME=${l_projects_name[$l_select_id]}

echo -e '\033[0;31;1m'
echo '---------Project Select-------------'
echo "$PRJ_NAME"
echo -e '\033[0m'

echo
echo "1. start -debug"
echo "2. start -deamon"
echo "3. restart"
echo "4. reload"
echo "5. stop"
echo "6. status"
echo "7. kill"
read -p "" debug_version_select
echo
echo "${debug_version_select}"
case "$debug_version_select" in
        1)
        php start_$PRJ_NAME.php start
        ;;
        2)
        php start_$PRJ_NAME.php start -d
        ;;
        3)
        php start_$PRJ_NAME.php restart
        ;;
        4)
        php start_$PRJ_NAME.php reload
        ;;
        5)
        php start_$PRJ_NAME.php stop
        ;;
        6)
        php start_$PRJ_NAME.php status
        ;;
        7)
        php start_$PRJ_NAME.php kill
        ;;
        *)
        php start_$PRJ_NAME.php status

esac




