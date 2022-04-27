#!/bin/bash


if [ -d "/runonce" ]; then
	for file in /runonce/*.sh
	do
		chmod 777 "$file"
		echo "Running $file"
	  	"$file"
	  	status=$?
		if [ $status -ne 0 ]; then
			echo "Failed to start runonce $file: $status"
			exit $status
		fi
	done
fi

if [ -d "/svcs" ]; then
	for file in /svcs/*.sh
	do
		chmod 777 "$file"
	  	"$file" &
	  	status=$?
		if [ $status -ne 0 ]; then
			echo "Failed to start service $file: $status"
			exit $status
		fi
	done

	# Naive check runs checks once a minute to see if either of the processes exited.
	# This illustrates part of the heavy lifting you need to do if you want to run
	# more than one service in a container. The container exits with an error
	# if it detects that either of the processes has exited.
	# Otherwise it loops forever, waking up every 60 seconds

	while sleep 60; do
		echo "Running"
		for file in /svcs/*.sh
		do
			ps aux |grep $file |grep -q -v grep
	  		PROCESS_STATUS=$?
	  		if [ $PROCESS_STATUS -ne 0 ]; then
			    echo "Processes $file has already exited."
			    "$file" &
			fi
		done
	done
fi
