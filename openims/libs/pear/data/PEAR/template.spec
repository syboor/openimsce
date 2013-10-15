%define peardir %(pear config-get php_dir 2> /dev/null || echo %{_datadir}/pear)

Summary: PEAR: @summary@
Name: @rpm_package@
Version: @version@
Release: @release@
License: @release_license@
Group: Development/Libraries
Source: http://@master_server@/get/@package@-%{version}.tgz
BuildRoot: %{_tmppath}/%{name}-root
URL: http://@master_server@/package/@package@
Prefix: %{_prefix}
BuildArchitectures: @arch@
@extra_headers@

%description
@description@

%prep
rm -rf %{buildroot}/*
%setup -c -T
# XXX Source files location is missing here in pear cmd
pear -v -c %{buildroot}/pearrc \
        -d php_dir=%{peardir} \
        -d doc_dir=/docs \
        -d bin_dir=%{_bindir} \
        -d data_dir=%{peardir}/data \
        -d test_dir=%{peardir}/tests \
        -d ext_dir=%{_libdir} \@extra_config@
        -s

%build
echo BuildRoot=%{buildroot}

%postun
# if refcount = 0 then package has been removed (not upgraded)
if [ "$1" -eq "0" ]; then
    pear uninstall --nodeps --ignore-errors --register-only @possible_channel@@package@
fi


%post
# if refcount = 2 then package has been upgraded
if [ "$1" -ge "2" ]; then
    pear upgrade --nodeps --soft --force --register-only @rpm_xml_dir@/@package@.xml
else
    pear install --nodeps --soft --force --register-only @rpm_xml_dir@/@package@.xml
fi

%install
pear -c %{buildroot}/pearrc install --nodeps -R %{buildroot} \
        $RPM_SOURCE_DIR/@package@-%{version}.tgz

rm %{buildroot}/pearrc
rm %{buildroot}/%{peardir}/.filemap
rm %{buildroot}/%{peardir}/.lock
rm -rf %{buildroot}/%{peardir}/.registry
rm -rf %{buildroot}/%{peardir}/.channels
rm %{buildroot}/%{peardir}/.depdb
rm %{buildroot}/%{peardir}/.depdblock

if [ "@doc_files@" != "" ]; then
     mv %{buildroot}/docs/@package@/* .
     rm -rf %{buildroot}/docs
fi

mkdir -p %{buildroot}@rpm_xml_dir@
tar -xzf $RPM_SOURCE_DIR/@package@-%{version}.tgz package@package2xml@.xml
cp -p package@package2xml@.xml %{buildroot}@rpm_xml_dir@/@package@.xml

%files
    %defattr(-,root,root)
    %doc @doc_files@
    %{peardir}/*
    @rpm_xml_dir@/@package@.xml
