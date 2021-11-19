rm -fr /tmp/* 2>/dev/null
apt-get clean
apt-get autoclean
rm -fr /var/lib/apt/lists/* 2>/dev/null
rm -fr /var/cache/* 2>/dev/null
rm /var/log/dpkg.log  2>/dev/null
rm /var/log/alternatives.log
rm /var/log/apt/*.log 2>/dev/null
rm /var/log/bootstrap.log 2>/dev/null
rm -fr /usr/share/doc/* 2>/dev/null
rm -fr /usr/share/man/* 2>/dev/null
rm -fr /var/lib/apt/lists/* 2>/dev/null 
rm -fr /usr/src/* 2>/dev/null 