#!/bin/bash
#
# Developed by: Sumit/Ian 4 May 2022
#
# Initial Global Parameters
#
keyname="EncryptionKey"
filename="/.env" # original env file path
outfilename=".env"
outfoldername="env_encryption"

#
# Use SHA256 and get RDS 2048 Key
#
ssh-keygen -f $keyname -N ''

#
# Convert RDS Key into public/private key
#
openssl rsa -in $keyname -outform pem > $keyname.pem
openssl rsa -in $keyname -pubout -outform pem > $keyname.pub.pem

#
# Generate a random salt
#
openssl rand -base64 128 > $keyname.bin

#
# Encrypt the key
#
openssl rsautl -encrypt -inkey $keyname.pub.pem -pubin -in $keyname.bin -out $keyname.bin.enc

#
# Encrypt a given file
#
openssl enc -aes-256-cbc -salt -in $filename -out $outfilename.enc -pass file:./$keyname.bin

#
# Move resulting files to application/env_encryption
#

if [ -d "../../$outfoldername/" ]; then
   echo "Folder already exits"
else
   mkdir ../../$outfoldername/
fi

cp $keyname.bin ../../$outfoldername/
cp $outfilename.enc ../../$outfoldername/

###########################

# #
# # Decrypt a give file
# # @inp: .env.enc and .bin
# #

# openssl rsautl -decrypt -inkey $keyname.pem -in $keyname.bin.enc -out $keyname.bin

# # openssl enc -d -aes-256-cbc -in $outfilename.enc -out $outfilename.DECRYPT.env -pass file:./$keyname.bin

# out=$($(openssl enc -d -aes-256-cbc -in $outfilename.enc -pass file:./$keyname.bin 2>&1)//$\n)

