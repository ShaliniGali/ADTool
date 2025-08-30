cp #!/bin/bash

A=(`ls ./`)


function ls_dir {
    C=$@
    
    for I in $C
    do
        #echo $I
        if [[ -d $I ]]
        then
            echo "DIR $I"
            B=`ls $I`
            
            cd $I

            ls_dir ${B[@]}

            cd ..
        elif [[ -f $I ]]
        then
            echo $PWD
            echo $I

            P=`php -l $I`

            if [[ $? != 0 ]]
            then
                echo "$I did not work"
                exit 1
            fi
        fi
    done

    return 0
}

ls_dir ${A[@]}
