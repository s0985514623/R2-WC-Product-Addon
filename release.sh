#!/bin/bash

release_folder='r2-wc-product-addon-release'
release_zip='r2-wc-product-addon-release.zip'
folder_array=("inc" "js/dist")
file_array=("plugin.php" "README.md" "LICENSE" ".env.production")
full_version=$(grep -oP "Version: \K.*" plugin.php)

# 删除旧的 release 文件
rm -f ../$release_zip

# 创建发布目录（如果不存在）
mkdir -p ../$release_folder

# 使用for循环遍历数组并执行指令
for folder in "${folder_array[@]}"; do

	rm -rf ../$release_folder/$folder
	# 创建目标文件夹（如果不存在）
  mkdir -p ../$release_folder/$folder
  cp -r ./$folder/* ../$release_folder/$folder
done

for file in "${file_array[@]}"; do
	rm -rf ../$release_folder/$file
  cp ./$file ../$release_folder/$file
done

# rename folder
# mv ../$release_folder ../$release_folder-v$full_version

# create zip
# zip -r ../$release_zip ../$release_folder

echo '✅ Release created'