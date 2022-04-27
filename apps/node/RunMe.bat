docker build -t node16 .

docker run --name node16 --privileged --cap-add SYS_ADMIN --cap-add DAC_READ_SEARCH --network basiccompose_infonet --dns=172.25.0.3 --dns=10.110.49.21 --dns=10.136.111.21 --dns=8.8.8.8 --dns=8.8.4.4 node16