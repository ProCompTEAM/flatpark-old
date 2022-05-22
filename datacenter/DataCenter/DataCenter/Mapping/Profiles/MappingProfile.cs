using AutoMapper;
using DataCenter.Data.Dtos;
using DataCenter.Data.Models;

namespace DataCenter.Common.Mapping.Profiles
{
    public class MappingProfile : Profile
    {
        public MappingProfile()
        {
            CreateMap<User, UserDto>().ReverseMap();

            CreateMap<UserSettings, UserSettingsDto>().ReverseMap();

            CreateMap<MapPoint, MapPointDto>().ReverseMap();

            CreateMap<FloatingText, FloatingTextDto>().ReverseMap();

            CreateMap<Credentials, CredentialsDto>().ReverseMap();

            CreateMap<UserBanRecord, UserBanRecordDto>().ReverseMap();
        }
    }
}
