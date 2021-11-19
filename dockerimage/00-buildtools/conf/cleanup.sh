rm -fr /tmp/* 2>/dev/null
apt-get clean
apt-get autoclean
rm -rf /var/lib/apt/lists/* 2>/dev/null
rm -rf /var/cache/* 2>/dev/null
rm /var/log/dpkg.log  2>/dev/null
rm /var/log/alternatives.log
rm /var/log/apt/*.log 2>/dev/null
rm /var/log/bootstrap.log 2>/dev/null
rm -rf /usr/share/doc/* 2>/dev/null
rm -rf /usr/share/man/* 2>/dev/null
rm -rf /var/lib/apt/lists/* 2>/dev/null 
find /usr/local/bin /usr/local/sbin /usr/local/mysql/bin -type f -exec strip --strip-all '{}' + || true;